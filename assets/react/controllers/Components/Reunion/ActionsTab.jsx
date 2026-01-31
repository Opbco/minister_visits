import React, { useState, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { connect } from 'react-redux';
import { useTranslation } from 'react-i18next';
import { useFormik } from 'formik';
import * as Yup from 'yup';
// Material UI Components
import {
    Box, Typography, Grid, Chip, Button,
    Avatar, Dialog, DialogTitle, DialogContent, DialogActions,
    TextField, FormControl, InputLabel, Select, MenuItem, Card, CardContent, Alert,
} from '@mui/material';

// Icons
import {
    Edit as EditIcon,
    Schedule as ScheduleIcon,
} from '@mui/icons-material';
import { getStatusAction } from '../../../utils/Globals';

const ActionsTab = ({ meeting, currentUser, updateAction }) => {
    const { t } = useTranslation();
    const [editingAction, setEditingAction] = useState(null);

    const formik = useFormik({
        enableReinitialize: true,
        initialValues: {
            description: editingAction ? editingAction.description : '', // Note: description might be HTML
            dateEcheance: editingAction ? (editingAction.dateEcheance?.split('T')[0]) : '',
            statut: editingAction ? editingAction.statut : 'pending',
            commentaire: editingAction ? editingAction.commentaire : ''
        },
        validationSchema: Yup.object({
            dateEcheance: Yup.date().nullable().required(t('date_echeance_required')),
            statut: Yup.string().required(t('statut_required')),
        }),
        onSubmit: (values) => {
            // Keep existing description if not edited in this simple form (or add a rich text editor)
            updateAction(editingAction.id, { 
            ...values,
            description: editingAction.description // Assuming we don't edit description text here for simplicity, or use values.description if input exists
            });
            setEditingAction(null);
            formik.resetForm();
        },
    });

    return (
    <Box>
        <Grid container spacing={2}>
        {meeting.actionItems?.map((action) => {
            const isResponsible = currentUser?.id === action.responsable?.userAccount?.id;

            return (
            <Grid item xs={12} sm={6} key={action.id}>
                <Card sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <CardContent sx={{ flexGrow: 1 }}>
                    <Box display="flex" justifyContent="space-between" mb={1}>
                    <Chip 
                        label={t(action.statut)} 
                        color={getStatusAction(action.statut, t)?.color || "default"}
                        size="small" 
                    />
                    {action.dateEcheance && (
                        <Typography variant="caption" color="error">
                        <ScheduleIcon fontSize="inherit" sx={{ verticalAlign: 'middle' }} /> 
                        {' ' + new Date(action.dateEcheance).toLocaleDateString()}
                        </Typography>
                    )}
                    </Box>

                    <Box 
                        dangerouslySetInnerHTML={{ __html: action.description }} 
                        sx={{ maxHeight: 200, overflowY: 'auto', bgcolor: '#f9f9f9', p: 2, borderRadius: 1 }}
                    />

                    <Box mt={2} display="flex" alignItems="center">
                    <Avatar sx={{ width: 24, height: 24, mr: 1, fontSize: '0.8rem' }}>
                        {action.responsable?.nomComplet?.charAt(0)}
                    </Avatar>
                    <Typography variant="caption" color="textSecondary">
                        {action.responsable?.nomComplet}
                    </Typography>
                    </Box>

                    {action.commentaire && (
                    <Alert severity="info" sx={{ mt: 1, py: 0 }}>
                        <Typography variant="caption">{action.commentaire}</Typography>
                    </Alert>
                    )}
                </CardContent>
                
                {isResponsible && (
                    <Box p={1} bgcolor="#f5f5f5" display="flex" justifyContent="flex-end">
                    <Button 
                        size="small" 
                        startIcon={<EditIcon />} 
                        onClick={() => setEditingAction(action)}
                    >
                        {t('Mettre à jour')}
                    </Button>
                    </Box>
                )}
                </Card>
            </Grid>
            );
        })}
        {meeting.actionItems?.length === 0 && (
            <Grid item xs={12}>
            <Alert severity="info">{t('Aucune action assignée pour cette réunion.')}</Alert>
            </Grid>
        )}
        </Grid>

        {/* Edit Action Dialog */}
        <Dialog open={Boolean(editingAction)} onClose={() => setEditingAction(null)} maxWidth="sm" fullWidth>
        <form onSubmit={formik.handleSubmit}>
            <DialogTitle>{t('Mise à jour de l\'action')}</DialogTitle>
            <DialogContent>
            <Box py={1}>
                <Typography variant="body2" color="textSecondary" gutterBottom>
                {t('Description')}:
                </Typography>
                <Box 
                bgcolor="#f0f0f0" p={1} borderRadius={1} mb={2} 
                dangerouslySetInnerHTML={{ __html: editingAction?.description }} 
                />

                <Grid container spacing={2}>
                <Grid item xs={6}>
                    <TextField
                    fullWidth
                    type="date"
                    name="dateEcheance"
                    label={t('Échéance')}
                    InputLabelProps={{ shrink: true }}
                    value={formik.values.dateEcheance}
                    onChange={formik.handleChange}
                    disabled={true}
                    error={formik.touched.dateEcheance && Boolean(formik.errors.dateEcheance)}
                    />
                </Grid>
                <Grid item xs={6}>
                    <FormControl fullWidth>
                    <InputLabel>{t('Statut')}</InputLabel>
                    <Select
                        name="statut"
                        label={t('Statut')}
                        value={formik.values.statut}
                        onChange={formik.handleChange}
                    >
                        <MenuItem value="pending">{t('En attente')}</MenuItem>
                        <MenuItem value="in_progress">{t('En cours')}</MenuItem>
                        <MenuItem value="completed">{t('Terminé')}</MenuItem>
                        <MenuItem value="cancelled">{t('Annulé')}</MenuItem>
                    </Select>
                    </FormControl>
                </Grid>
                <Grid item xs={12}>
                    <TextField
                    fullWidth
                    multiline
                    rows={2}
                    name="commentaire"
                    label={t('Commentaire d\'avancement')}
                    value={formik.values.commentaire}
                    onChange={formik.handleChange}
                    />
                </Grid>
                </Grid>
            </Box>
            </DialogContent>
            <DialogActions>
            <Button onClick={() => setEditingAction(null)} color="secondary">
                {t('Annuler')}
            </Button>
            <Button type="submit" variant="contained" color="primary">
                {t('Sauvegarder')}
            </Button>
            </DialogActions>
        </form>
        </Dialog>
    </Box>
    );
}

export default ActionsTab
