import React from "react"; // No useState needed for Redux-managed states
import {
  Container,
  Box,
  Typography,
  TextField,
  Button,
  CircularProgress,
  Paper,
  Alert,
  AlertTitle,
} from "@mui/material";
import { useFormik } from "formik";
import * as Yup from "yup";
import { connect } from "react-redux";
import { loginUser, clearErrors } from "./../redux/actions/UserActions";
import { useTranslation } from "react-i18next";

const SignIn = ({ isLoading, error, clearErrors, loginUser }) => {
  const { t } = useTranslation();
  // Define the validation schema using Yup
  const validationSchema = Yup.object({
    username: Yup.string().required(t("required", { ns: "login" })),
    password: Yup.string().required(t("error_pass_required", { ns: "login" })),
  });

  // Initialize Formik for form management
  const formik = useFormik({
    initialValues: {
      username: "",
      password: "",
    },
    validationSchema: validationSchema,
    onSubmit: async (values) => {
      await loginUser(values);
    },
  });

  return (
    <Box
      sx={{
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        minHeight: "100vh",
        backgroundImage:
          "url(./images/login-backg.jpg)", 
        backgroundSize: "cover",
        backgroundPosition: "center",
        backgroundRepeat: "no-repeat",
        position: "relative",
      }}
    >
      <Box
        sx={{
          position: "absolute",
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: "rgba(0, 0, 0, 0.5)",
          zIndex: 1,
        }}
      />
      <Container
        component="main"
        maxWidth="sm"
        sx={{
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          justifyContent: "center",
          padding: { xs: 2, sm: 3 },
          zIndex: 2,
        }}
      >
        <Paper
          elevation={6}
          sx={{
            padding: { xs: 3, sm: 5 },
            borderRadius: "16px",
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            width: "100%",
            boxShadow: "0px 10px 25px rgba(0, 0, 0, 0.3)",
            backgroundColor: "#ffffff",
          }}
        >
          <Box sx={{ mb: 4, textAlign: "center" }}>
            <Typography
              component="h1"
              variant="h4"
              sx={{
                fontWeight: 700,
                color: "#3f51b5", // Primary color for title
                mb: 1,
                fontFamily: "Inter, sans-serif",
              }}
            >
              {t("appName")}
            </Typography>
            <Typography
              variant="body1"
              color="text.secondary"
              sx={{ fontFamily: "Inter, sans-serif" }}
            >
              {t("title", { ns: "login" })}
            </Typography>
          </Box>

          {error && (
            <Alert
              severity="error"
              sx={{ width: "100%", mt: 3, mb: 2 }}
              onClose={() => {
                clearErrors();
              }}
            >
              <AlertTitle>{t("error", { ns: "login" })}</AlertTitle>
              <strong>{error}</strong>
            </Alert>
          )}

          <Box
            component="form"
            onSubmit={formik.handleSubmit}
            noValidate
            sx={{ width: "100%" }}
          >
            <TextField
              margin="normal"
              required
              fullWidth
              id="username"
              label={t("username", { ns: "login" })}
              name="username"
              autoComplete="username"
              autoFocus
              value={formik.values.username}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.touched.username && Boolean(formik.errors.username)}
              helperText={formik.touched.username && formik.errors.username}
              sx={{
                "& .MuiOutlinedInput-root": { borderRadius: "10px" },
                fontFamily: "Inter, sans-serif",
              }}
            />
            <TextField
              margin="normal"
              required
              fullWidth
              name="password"
              label={t("password", { ns: "login" })}
              type="password"
              id="password"
              autoComplete="current-password"
              value={formik.values.password}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.touched.password && Boolean(formik.errors.password)}
              helperText={formik.touched.password && formik.errors.password}
              sx={{
                "& .MuiOutlinedInput-root": { borderRadius: "10px" },
                fontFamily: "Inter, sans-serif",
              }}
            />

            <Button
              type="submit"
              fullWidth
              variant="contained"
              sx={{
                mt: 3,
                mb: 2,
                py: 1.5,
                borderRadius: "10px",
                backgroundColor: "#3f51b5",
                "&:hover": {
                  backgroundColor: "#303f9f",
                },
                fontFamily: "Inter, sans-serif",
                fontWeight: 600,
              }}
              disabled={isLoading}
            >
              {isLoading ? (
                <CircularProgress size={24} color="inherit" />
              ) : (
                t("signIn", { ns: "login" })
              )}
            </Button>
          </Box>
        </Paper>
      </Container>
    </Box>
  );
};

const mapStateToProps = (state) => ({
  error: state.auth.errors,
  isLoading: state.auth.loading,
});

const mapActionsToProps = {
  loginUser,
  clearErrors,
};

export default connect(mapStateToProps, mapActionsToProps)(SignIn);
