import React from 'react'
import { useTranslation } from 'react-i18next';

// Material UI Components
import {
    Box, Typography, Grid, Button,
    Avatar, Divider, Card, CardContent, CardActions
} from '@mui/material';

// Icons
import {
    Download as DownloadIcon,  
    AttachFile as AttachFileIcon  
} from '@mui/icons-material';

const DocumentsTab = ({ meeting }) => {
    const { t } = useTranslation();

    if (!meeting.documents || meeting.documents.length === 0) {
        return (
        <Box display="flex" flexDirection="column" alignItems="center" py={4} color="text.secondary">
            <AttachFileIcon sx={{ fontSize: 60, opacity: 0.3, mb: 2 }} />
            <Typography>{t('no_documents_joint_found')}</Typography>
        </Box>
        );
    }

    return (
        <Box>
        <Typography variant="h6" gutterBottom>{t('Documents joints')}</Typography>
        <Grid container spacing={2}>
            {meeting.documents.map((doc) => {
            const displayName = doc.originalFileName || doc.fileName || t('document_without_name');
            const ext = displayName.split('.').pop().toUpperCase();
            const size = doc.fileSize ? `${(doc.fileSize / 1024).toFixed(2)} KB` : 'N/A';

            return (
                <Grid item xs={12} sm={6} md={4} key={doc.id}>
                <Card variant="outlined" sx={{ '&:hover': { boxShadow: 3 } }}>
                    <CardContent>
                    <Box display="flex" alignItems="flex-start">
                        <Avatar variant="rounded" sx={{ bgcolor: 'secondary.light', color: 'secondary.dark', mr: 2 }}>
                        {ext.slice(0, 3)}
                        </Avatar>
                        <Box overflow="hidden">
                        <Typography variant="subtitle2" noWrap title={displayName}>
                            {displayName}
                        </Typography>
                        <Typography variant="caption" color="textSecondary">
                            {size} • {new Date(doc.updated || Date.now()).toLocaleDateString()}
                        </Typography>
                        </Box>
                    </Box>
                    </CardContent>
                    <Divider />
                    <CardActions sx={{ justifyContent: 'flex-end', bgcolor: '#fafafa' }}>
                    <Button 
                        size="small" 
                        startIcon={<DownloadIcon />}
                        href={doc.fileWebPath} 
                        target="_blank"
                        download
                    >
                        {t('download')}
                    </Button>
                    </CardActions>
                </Card>
                </Grid>
            );
            })}
        </Grid>
        </Box>
    );
};

export default DocumentsTab
