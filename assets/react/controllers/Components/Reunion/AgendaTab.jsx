import React from 'react';
import { useTranslation } from 'react-i18next';
// Material UI Components
import {
    Box, Typography, Chip, Avatar,  Alert,
    Stepper, Step, StepLabel, StepContent
} from '@mui/material';

// Icons
import {
    Person as PersonIcon,
    Schedule as ScheduleIcon,
} from '@mui/icons-material';

const AgendaTab = ({ meeting }) => {
    const { t } = useTranslation();
    
    return (
        <Box>
            <Stepper orientation="vertical">
                {meeting.agendaItems?.map((item, index) => (
                <Step key={item.id} active={true}>
                    <StepLabel 
                    icon={<Avatar sx={{ width: 24, height: 24, fontSize: '0.8rem', bgcolor: 'primary.main' }}>{index + 1}</Avatar>}
                    >
                    <Typography variant="subtitle1" fontWeight="bold">{item.titre}</Typography>
                    </StepLabel>
                    <StepContent>
                    <Typography variant="body2" color="textSecondary" gutterBottom>
                        {item.description}
                    </Typography>
                    <Box display="flex" gap={2} mt={1}>
                        {item.dureeEstimee && (
                        <Chip 
                            icon={<ScheduleIcon />} 
                            label={`${item.dureeEstimee} min`} 
                            size="small" 
                            variant="outlined" 
                        />
                        )}
                        {item.presentateur && (
                        <Chip 
                            icon={<PersonIcon />} 
                            label={item.presentateur?.personnel?.nomComplet || "Intervenant"} 
                            size="small" 
                            color="secondary" 
                            variant="outlined" 
                        />
                        )}
                    </Box>
                    </StepContent>
                </Step>
                ))}
            </Stepper>
            {(!meeting.agendaItems || meeting.agendaItems.length === 0) && (
                <Alert severity="info">{t('Aucun ordre du jour défini.')}</Alert>
            )}
        </Box>
    );
}

export default AgendaTab
