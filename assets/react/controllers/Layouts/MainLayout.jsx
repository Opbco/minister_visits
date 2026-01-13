import React, { useState, useMemo } from "react";
import { useTranslation } from "react-i18next";
import { Outlet } from "react-router-dom";
import { connect } from "react-redux";
import { CssBaseline, ThemeProvider, Box } from "@mui/material";
import { createTheme, responsiveFontSizes, styled } from "@mui/material/styles";
import Snackbar from "@mui/material/Snackbar";
import {
  setInfoMsg,
  clearInfoMsg,
  changeMode,
} from "./../redux/actions/configActions";
import MuiAlert from "@mui/material/Alert";
import getLPTheme from "../../utils/getLPTheme";
import Footer from "./Footer";
import Sidebar from "./Sidebar";
import CONSTANTS from "../../utils/Constants";
import Header from "./Header";
import ErrorBoundary from "./../../utils/ErrorBoundary";


const drawerWidth = CONSTANTS.drawerWidth || 280; // Default to 280 if not defined in constants

const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

// Styled component for the main content area
const Main = styled('main', { shouldForwardProp: (prop) => true })(
  ({ theme, open }) => ({
    flexGrow: 1,
    padding: theme.spacing(3),
    marginTop: theme.spacing(8),
    transition: theme.transitions.create('margin', {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.leavingScreen,
    }),
    marginLeft: open ? `calc(${theme.spacing(7)} + 1px)` : drawerWidth, // Default margin when closed (icon width)
    [theme.breakpoints.down('sm')]: {
      marginLeft: `calc(${theme.spacing(8)} + 1px)`,
    },
    ...(open && {
      transition: theme.transitions.create('margin', {
        easing: theme.transitions.easing.sharp,
        duration: theme.transitions.duration.enteringScreen,
      }),
    }),
  }),
);

const MainLayout = (props) => {
  const { t } = useTranslation();
  const [open, setOpen] = useState(false); // State for sidebar open/close

  const theme = useMemo(
    () => createTheme(getLPTheme(props.mode)),
    [props.mode]
  );

  return (
    <>
      <ThemeProvider theme={responsiveFontSizes(theme)}>
        <Box sx={{ display: "flex", flexDirection: "column", minHeight: "100vh" }}>
          <CssBaseline />
          <Header toggleColorMode={props.changeMode} />
          <Sidebar collapsed={open} onToggleCollapse={() => setOpen(!open)} user={props.user}  />
          <Main open={open}>
            <ErrorBoundary>
              <Outlet />
            </ErrorBoundary>
          </Main>
          <Footer open={open} />
        </Box>
        <Snackbar
          anchorOrigin={{ vertical: "top", horizontal: "center" }}
          open={props.snackOpen}
          autoHideDuration={6000}
          onClose={props.clearInfoMsg}
        >
          <Alert
            onClose={props.clearInfoMsg}
            severity={props.infoSeverity}
            sx={{ width: "100%" }}
          >
            {props.messageInfo}
          </Alert>
        </Snackbar>
      </ThemeProvider>
    </>
  );
};

const mapStateToProps = (state) => ({
  mode: state.config.mode,
  messageInfo: state.config.infoMessage,
  user: state.auth.credentials,
  snackOpen: state.config.snackOpen,
  infoSeverity: state.config.infoSeverity,
});

const mapActionsToProps = {
  setInfoMsg,
  clearInfoMsg,
  changeMode,
};

export default connect(mapStateToProps, mapActionsToProps)(MainLayout);