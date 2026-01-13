import React from "react";
import { Alert, AlertTitle } from "@mui/material";

const Error = (props) => {
  return (
    <Alert severity="error" sx={{ width: "100%", mt: 3, mb: 2 }}>
      <AlertTitle>Error</AlertTitle>
      <strong>{props.message}</strong>
    </Alert>
  );
};

export default Error;