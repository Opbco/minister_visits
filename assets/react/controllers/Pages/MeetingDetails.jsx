import React, { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { connect } from 'react-redux';
import { useTranslation } from 'react-i18next';
// Material UI Components
import {
    Box, Paper, Typography, Tabs, Tab, Chip,
    IconButton, LinearProgress, Alert, Badge
} from '@mui/material';

// Icons
import {
    Event as EventIcon,
    Person as PersonIcon,
    ArrowBack as ArrowBackIcon,
    TaskAlt as TaskIcon,
    Info as InfoIcon,
    AttachFile as AttachFileIcon 
} from '@mui/icons-material';

// API Hooks (Assuming these exist based on your setup)
import { 
    useGetReunionByIdQuery, 
    useUpdateReunionParticipationMutation, 
    useUpdateActionItemMutation 
} from '../redux/services/openApi';
import GeneralInfoTab from '../Components/Reunion/GeneralInfoTab';
import ParticipantsTab from '../Components/Reunion/ParticipantsTab';
import AgendaTab from '../Components/Reunion/AgendaTab';
import ActionsTab from '../Components/Reunion/ActionsTab';
import { getStatusConfig } from '../../utils/Globals';
import DocumentsTab from '../Components/Reunion/DocumentsTab';


// --- MAIN COMPONENT ---

const MeetingDetails = ({ user }) => {
    const { t } = useTranslation();
    const { reunionId } = useParams();
    const navigate = useNavigate();
    const [tabValue, setTabValue] = useState(0);

    // 1. Fetch Data
    const { data: meeting, isLoading, error, refetch } = useGetReunionByIdQuery(reunionId);
    
    // 2. Mutations
    const [updateParticipation] = useUpdateReunionParticipationMutation();
    const [updateActionItem] = useUpdateActionItemMutation();

    const handleTabChange = (event, newValue) => {
        setTabValue(newValue);
    };

    const handleUpdateStatus = async (participationId, newStatus, reason = null) => {
        try {
        await updateParticipation({ 
            id: participationId, 
            data: { status: newStatus, absenceReason: reason } 
        }).unwrap();
        refetch(); // Refresh data to show updates
        } catch (err) {
        console.error("Failed to update status", err);
        }
    };

    const handleUpdateAction = async (actionId, data) => {
        try {
        await updateActionItem({ 
            id: actionId, 
            data: data 
        }).unwrap();
        refetch();
        } catch (err) {
        console.error("Failed to update action", err);
        }
    };

    if (isLoading) return <LinearProgress />;
    if (error) return <Alert severity="error">{t('error_loading_meeting')}</Alert>;
    if (!meeting) return <Alert severity="warning">{t('no_meeting_found')}</Alert>;

    return (
        <Box sx={{ maxWidth: 1200, margin: '0 auto', p: 2 }}>
        {/* Header */}
        <Box mb={3} display="flex" alignItems="center">
            <IconButton onClick={() => navigate(-1)} sx={{ mr: 2 }}>
            <ArrowBackIcon />
            </IconButton>
            <Box flexGrow={1}>
            <Typography variant="h4" component="h1" fontWeight="bold" color="primary">
                {meeting.objet}
            </Typography>
            <Box display="flex" alignItems="center" gap={2} mt={1}>
                <Chip label={meeting.type} color="secondary" size="small" />
                <Chip 
                label={getStatusConfig(meeting.statut, t)?.label || "Statut inconnu"} 
                color={getStatusConfig(meeting.statut, t)?.color || "default"} 
                variant="outlined" 
                size="small" 
                />
            </Box>
            </Box>
        </Box>

        {/* Tabs Navigation */}
        <Paper sx={{ mb: 3 }}>
            <Tabs 
                value={tabValue} 
                onChange={handleTabChange} 
                variant="scrollable"
                scrollButtons="auto"
                textColor="primary"
                indicatorColor="primary"
            >
                <Tab label={t('general_information')} icon={<InfoIcon />} iconPosition="start" />
                <Tab label={t('participants')} icon={<PersonIcon />} iconPosition="start" />
                <Tab label={t('agenda')} icon={<EventIcon />} iconPosition="start" />
                <Tab 
                    label={
                    <Badge badgeContent={meeting.actionItems?.length} color="error" invisible={!meeting.actionItems?.length}>
                        {t('actions')} &nbsp;
                    </Badge>
                    } 
                    icon={<TaskIcon />} 
                    iconPosition="start" 
                />
                <Tab 
                    label={
                    <Badge badgeContent={meeting.documents?.length} color="primary" invisible={!meeting.documents?.length}>
                        {t('Documents')} &nbsp;
                    </Badge>
                    }
                    icon={<AttachFileIcon />} 
                    iconPosition="start" 
                />
            </Tabs>
        </Paper>

        {/* Tab Panels */}
        <Box sx={{ py: 2 }}>
            {tabValue === 0 && <GeneralInfoTab meeting={meeting} />}
            
            {tabValue === 1 && (
            <ParticipantsTab 
                meeting={meeting} 
                currentUser={user} 
                updateStatus={handleUpdateStatus} 
            />
            )}
            
            {tabValue === 2 && <AgendaTab meeting={meeting} />}
            
            {tabValue === 3 && (
            <ActionsTab 
                meeting={meeting} 
                currentUser={user} 
                updateAction={handleUpdateAction} 
            />
            )}
            {tabValue === 4 && <DocumentsTab meeting={meeting} />}
        </Box>
        </Box>
    );
};

const mapStateToProps = (state) => ({
    user: state.auth.credentials,
});

export default connect(mapStateToProps)(MeetingDetails);