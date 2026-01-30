import React, { useState, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { connect } from 'react-redux';
import { useTranslation } from 'react-i18next';
import { useFormik } from 'formik';
import * as Yup from 'yup';
// Material UI Components
import {
    Box, Paper, Typography, Chip, Button,
    Avatar, List, ListItem, ListItemAvatar, ListItemText,
    ListItemSecondaryAction, Divider,
    Dialog, DialogTitle, DialogContent, DialogActions,
    TextField, FormControl, Select, MenuItem,
} from '@mui/material';

// Icons
import {
    Info as InfoIcon
} from '@mui/icons-material';

const getParticipantStatusColor = (status) => {
    const map = {
        'invited': 'default',
        'confirmed': 'info',
        'attended': 'success',
        'absent': 'error',
        'excused': 'warning'
    };
    return map[status] || 'default';
};

const ParticipantsTab = ({ meeting, currentUser, updateStatus }) => {
    const { t } = useTranslation();
    const [selectedParticipation, setSelectedParticipation] = useState(null);
    const [excuseDialogOpen, setExcuseDialogOpen] = useState(false);

    const meetingHasStarted = useMemo(() => {
    // Logic: Absent/Attended only valid if meeting has started
    return new Date(meeting.dateDebut) <= new Date();
    }, [meeting.dateDebut]);

    const validationSchema = Yup.object({
    reason: Yup.string().required(t('Le motif est obligatoire pour être excusé.'))
    });
    
    const formik = useFormik({
        initialValues: { reason: '' },
        validationSchema: validationSchema,
        onSubmit: (values) => {
            updateStatus(selectedParticipation.id, 'excused', values.reason);
            setExcuseDialogOpen(false);
            formik.resetForm();
        },
    });
    
    const handleStatusChange = (participation, newStatus) => {
        if (newStatus === 'excused') {
            setSelectedParticipation(participation);
            setExcuseDialogOpen(true);
        } else {
            updateStatus(participation.id, newStatus);
        }
    };
    
    return (
    <Box>
        <Box mb={2} display="flex" justifyContent="space-between" alignItems="center">
        <Typography variant="h6">{t('Liste des participants')}</Typography>
        <Chip 
            label={`${meeting.participations?.length || 0} ${t('Invités')}`} 
            color="primary" 
            variant="outlined" 
        />
        </Box>

        <Paper elevation={0} sx={{ border: '1px solid #e0e0e0' }}>
        <List>
            {meeting.participations?.map((part, index) => {
            const personName = part.personnel ? part.personnel.nomComplet : part.externalParticipant?.nom;
            const personRole = part.personnel ? part.personnel.fonction?.abbreviation : part.externalParticipant?.organisation;
            const isInternal = !!part.personnel;

            return (
                <React.Fragment key={part.id}>
                <ListItem alignItems="flex-start">
                    <ListItemAvatar>
                    <Avatar sx={{ bgcolor: isInternal ? 'primary.main' : 'secondary.main' }}>
                        {personName?.charAt(0)}
                    </Avatar>
                    </ListItemAvatar>
                    <ListItemText
                    primary={
                        <Box display="flex" alignItems="center" gap={1}>
                        <Typography variant="subtitle1" component="span">
                            {personName}
                        </Typography>
                        {!isInternal && <Chip label="Externe" size="small" variant="outlined" />}
                        </Box>
                    }
                    secondary={
                        <React.Fragment>
                        <Typography component="span" variant="body2" color="text.primary">
                            {personRole}
                        </Typography>
                        {part.absenceReason && (
                            <Box mt={0.5} color="warning.main" display="flex" alignItems="center">
                            <InfoIcon fontSize="inherit" sx={{ mr: 0.5 }} />
                            <Typography variant="caption">Motif: {part.absenceReason}</Typography>
                            </Box>
                        )}
                        </React.Fragment>
                    }
                    />
                    <ListItemSecondaryAction>
                    <FormControl size="small" sx={{ minWidth: 120 }}>
                        <Select
                        value={part.status}
                        onChange={(e) => handleStatusChange(part, e.target.value)}
                        variant="outlined"
                        sx={{ 
                            height: 32, 
                            fontSize: '0.875rem',
                            '& .MuiSelect-select': { py: 0.5 }
                        }}
                        >
                        <MenuItem value="invited">{t('Invité')}</MenuItem>
                        <MenuItem value="confirmed">{t('Confirmé')}</MenuItem>
                        
                        {/* Conditional Rendering based on date logic */}
                        <MenuItem value="attended" disabled={!meetingHasStarted}>
                            {t('Présent')}
                        </MenuItem>
                        <MenuItem value="absent" disabled={!meetingHasStarted}>
                            {t('Absent')}
                        </MenuItem>
                        
                        <MenuItem value="excused">{t('Excusé')}</MenuItem>
                        </Select>
                    </FormControl>
                    </ListItemSecondaryAction>
                </ListItem>
                {index < meeting.participations.length - 1 && <Divider variant="inset" component="li" />}
                </React.Fragment>
            );
            })}
        </List>
        </Paper>

        {/* Excused Dialog */}
        <Dialog open={excuseDialogOpen} onClose={() => setExcuseDialogOpen(false)}>
            <form onSubmit={formik.handleSubmit}>
                <DialogTitle>{t('Motif de l\'absence')}</DialogTitle>
                <DialogContent>
                <TextField
                    fullWidth
                    autoFocus
                    margin="dense"
                    id="reason"
                    name="reason"
                    label={t("Raison de l'absence")}
                    multiline
                    rows={3}
                    value={formik.values.reason}
                    onChange={formik.handleChange}
                    error={formik.touched.reason && Boolean(formik.errors.reason)}
                    helperText={formik.touched.reason && formik.errors.reason}
                />
                </DialogContent>
                <DialogActions>
                <Button onClick={() => setExcuseDialogOpen(false)} color="secondary">
                    {t('Annuler')}
                </Button>
                <Button type="submit" variant="contained" color="primary">
                    {t('Enregistrer')}
                </Button>
                </DialogActions>
            </form>
        </Dialog>
    </Box>
    );
}

export default ParticipantsTab
