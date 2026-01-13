import React from 'react'
import { Typography, Box, Button, Breadcrumbs, Link } from '@mui/material';
import { Link as RouterLink } from 'react-router-dom';

const PageHeader = ({ title, subtitle, buttonText, buttonIcon, onButtonClick, breadcrumbs }) => {
  return (
    <Box sx={{ mb: 4 }}>
      {breadcrumbs && (
        <Breadcrumbs aria-label="breadcrumb" sx={{ mb: 2 }}>
          <Link component={RouterLink} to="/" underline="hover" color="inherit">
            Tableau de bord
          </Link>
          {breadcrumbs.map((crumb, index) => (
            crumb.link ? (
              <Link 
                component={RouterLink} 
                to={crumb.link} 
                underline="hover" 
                color="inherit" 
                key={index}
              >
                {crumb.text}
              </Link>
            ) : (
              <Typography color="text.primary" key={index}>
                {crumb.text}
              </Typography>
            )
          ))}
        </Breadcrumbs>
      )}
      
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Box>
          <Typography variant="h4" component="h1" sx={{ fontWeight: 'bold' }}>
            {title}
          </Typography>
          {subtitle && (
            <Typography variant="subtitle1" color="text.secondary" sx={{ mt: 1 }}>
              {subtitle}
            </Typography>
          )}
        </Box>
        {buttonText && (
          <Button
            variant="contained"
            startIcon={buttonIcon}
            onClick={onButtonClick}
            sx={{ 
              '&:hover': {
                backgroundColor: '#067740ff',
              }
            }}
          >
            {buttonText}
          </Button>
        )}
      </Box>
    </Box>
  );
};

export default PageHeader;