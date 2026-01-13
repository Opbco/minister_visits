import React from 'react';
import { CircularProgress, Box, Typography } from '@mui/material';

const Waiting = ({message}) => {
  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center',
        alignItems: 'center',
        minHeight: '100vh', // Ensures the component takes full viewport height
        backgroundColor: '#f0f2f5', // Light background for better visibility
        color: '#333', // Darker text color
        fontFamily: 'Inter, sans-serif', // Using Inter font
        borderRadius: '8px', // Rounded corners for the container
        padding: '20px',
      }}
    >
      {/* CircularProgress component for the loading spinner */}
      <CircularProgress
        size={60} // Adjust the size of the spinner
        thickness={5} // Adjust the thickness of the spinner
        sx={{
          color: '#1976d2', // Material-UI primary color for the spinner
          marginBottom: '20px', // Space between spinner and text
        }}
      />
      {/* Typography component for the loading message */}
      <Typography
        variant="h6" // Heading variant for the text
        component="div" // Render as a div
        sx={{
          fontWeight: 'bold', // Bold text
          textAlign: 'center', // Center align the text
          borderRadius: '4px', // Rounded corners for text background
          padding: '8px 15px', // Padding around the text
          backgroundColor: 'rgba(255, 255, 255, 0.8)', // Slightly transparent white background for text
          boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)', // Subtle shadow
        }}
      >
       {message}
      </Typography>
    </Box>
  );
};

export default Waiting;
