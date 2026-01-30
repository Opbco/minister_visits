import React from 'react';
import { useTranslation } from 'react-i18next';
// Material UI Components
import {
    Box, Paper, Typography, Grid,
    Avatar, List, ListItem, ListItemAvatar,
    ListItemText, Divider, Card, CardContent,
    Alert,
    Button
} from '@mui/material';

// Icons
import {
    Event as EventIcon,
    Room as RoomIcon,
    Business as BusinessIcon,
    Person as PersonIcon,
    Schedule as ScheduleIcon,
    Description as DescriptionIcon,
    Download as DownloadIcon,
    Info as InfoIcon
} from '@mui/icons-material';
import { formatDate } from '../../../utils/Globals';

const GeneralInfoTab = ({ meeting }) => {
    const { t } = useTranslation();

    return (
        <Grid container spacing={3}>
            <Grid item xs={12} md={8}>
            <Paper elevation={0} sx={{ p: 3, border: '1px solid #e0e0e0' }}>
                <Typography variant="h6" gutterBottom color="primary">
                {t('Description & Contexte')}
                </Typography>
                <Box display="flex" alignItems="center" mb={2}>
                <BusinessIcon color="action" sx={{ mr: 2 }} />
                <Box>
                    <Typography variant="caption" color="textSecondary">{t('Structure Organisatrice')}</Typography>
                    <Typography variant="body1">{meeting.organisateur?.nameFr}</Typography>
                </Box>
                </Box>
                <Box display="flex" alignItems="center" mb={2}>
                <RoomIcon color="action" sx={{ mr: 2 }} />
                <Box>
                    <Typography variant="caption" color="textSecondary">{t('Lieu / Salle')}</Typography>
                    <Typography variant="body1">
                    {meeting.salle ? meeting.salle.nom : (meeting.lieu || 'Non défini')}
                    </Typography>
                </Box>
                </Box>
                
                <Divider sx={{ my: 2 }} />
                
                <Typography variant="subtitle2" gutterBottom>{t('Compte Rendu (Extrait)')}</Typography>
                <Box 
                dangerouslySetInnerHTML={{ __html: meeting.compteRendu || "<em>Aucun compte rendu disponible.</em>" }} 
                sx={{ maxHeight: 200, overflowY: 'auto', bgcolor: '#f9f9f9', p: 2, borderRadius: 1 }}
                />

                <Box mb={3}>
                    <Typography variant="subtitle2" gutterBottom display="flex" alignItems="center">
                    <DescriptionIcon fontSize="small" sx={{ mr: 1 }} />
                    {t('official_report')}
                    </Typography>
                    
                    {meeting.rapport ? (
                        <Paper 
                            variant="outlined" 
                            sx={{ p: 2, display: 'flex', alignItems: 'center', justifyContent: 'space-between', bgcolor: '#f0f7ff', borderColor: '#cfe8fc' }}
                        >
                            <Box display="flex" alignItems="center">
                            <DescriptionIcon color="primary" sx={{ mr: 2, fontSize: 30 }} />
                            <Box>
                                <Typography variant="body2" fontWeight="bold">
                                {meeting.rapport.originalFileName || meeting.rapport.fileName}
                                </Typography>
                                <Typography variant="caption" color="textSecondary">
                                {meeting.rapport.fileSize ? `${(meeting.rapport.fileSize / 1024).toFixed(2)} KB` : ''}
                                </Typography>
                            </Box>
                            </Box>
                            <Button 
                                variant="contained" 
                                color="primary" 
                                size="small"
                                startIcon={<DownloadIcon />}
                                href={meeting.rapport.fileWebPath} // Use the web path from API
                                target="_blank"
                                download
                            >
                                {t('download')}
                            </Button>
                        </Paper>
                    ) : (
                        <Alert severity="info" icon={<InfoIcon />}>
                            {t('the_official_report_is_not_available')}
                        </Alert>
                    )}
                </Box>
            </Paper>
            </Grid>
    
            <Grid item xs={12} md={4}>
            <Card elevation={0} sx={{ border: '1px solid #e0e0e0', bgcolor: '#f5faff' }}>
                <CardContent>
                <Typography variant="h6" gutterBottom color="primary">
                    {t('Détails Rapides')}
                </Typography>
                <List dense>
                    <ListItem>
                    <ListItemAvatar><Avatar><EventIcon /></Avatar></ListItemAvatar>
                    <ListItemText primary={t('Début')} secondary={formatDate(meeting.dateDebut)} />
                    </ListItem>
                    <ListItem>
                    <ListItemAvatar><Avatar><ScheduleIcon /></Avatar></ListItemAvatar>
                    <ListItemText primary={t('Fin')} secondary={formatDate(meeting.dateFin)} />
                    </ListItem>
                    <ListItem>
                    <ListItemAvatar><Avatar><PersonIcon /></Avatar></ListItemAvatar>
                    <ListItemText 
                        primary={t('Président')} 
                        secondary={meeting.president?.nomComplet || meeting.president || "N/A"} 
                    />
                    </ListItem>
                </List>
                </CardContent>
            </Card>
            </Grid>
        </Grid>
    );
}

export default GeneralInfoTab
