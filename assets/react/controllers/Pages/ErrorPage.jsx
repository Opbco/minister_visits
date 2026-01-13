import React from "react";
import { Container, Typography, Button } from "@mui/material";
import ErrorOutlineIcon from "@mui/icons-material/ErrorOutline";
import { useRouteError, useNavigate } from "react-router-dom";

const ErrorPage = () => {
  const error = useRouteError();
  const navigate = useNavigate();

  return (
    <Container
      component="main"
      maxWidth="xs"
      style={{ textAlign: "center", marginTop: "100px" }}
    >
      <ErrorOutlineIcon style={{ fontSize: 120, color: "red" }} />
      <Typography variant="h5" style={{ marginTop: "20px" }}>
        Oops! Something went wrong.
      </Typography>
      <Typography variant="body1" style={{ marginTop: "20px" }}>
        {error.statusText || error.message}
      </Typography>
      <Button variant="contained" color="primary" style={{ marginTop: "30px" }} onClick={() => navigate(-1)}>
        Go Back
      </Button>
    </Container>
  );
};

export default ErrorPage;
