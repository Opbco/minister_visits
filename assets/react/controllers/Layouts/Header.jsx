import React, { useState } from "react";
import {
  AppBar,
  Toolbar,
  Typography,
  Box,
  IconButton,
  Avatar,
  Chip,
  Stack,
  Menu,
  MenuItem,
  ListItemIcon,
  ListItemText,
  Button,
  Tooltip,
} from "@mui/material";
import { Notifications, Settings, Logout, Person, Security, Person2 } from "@mui/icons-material";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";
import ToggleColorMode from "./ToggleColorMode";
import LanguageSwitcher from "./LanguageSwitcher";
import { logoutUser } from "../redux/actions/UserActions";
import { connect } from "react-redux";
import IdleTimer from "../Components/IdleTimer";

const Header = ({
  toggleColorMode,
  mode,
  auth,
  user,
  currentYear,
  logoutUser,
}) => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [userMenuAnchor, setUserMenuAnchor] = useState(null);

  const handleUserMenuOpen = (event) => {
    setUserMenuAnchor(event.currentTarget);
  };

  const handleUserMenuClose = () => {
    setUserMenuAnchor(null);
  };

  const handleLogout = () => {
    logoutUser();
    handleUserMenuClose();
  };

  return (
    <AppBar
      position="fixed"
      sx={{ zIndex: (theme) => theme.zIndex.drawer + 1 }}
    >
      <IdleTimer onIdle={handleLogout} />
      <Toolbar>
        <Typography
          variant="h6"
          component="div"
          sx={{ flexGrow: 1, fontWeight: "bold", fontSize: "2rem" }}
        >
          {t("appName")}
        </Typography>

        <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
          <Stack direction="row" spacing={1} alignItems="center">
            <Chip
              label={user?.username}
              size="small"
              variant="outlined"
              sx={{ color: "white", borderColor: "white" }}
            />
            <Typography variant="body2" sx={{ color: "white" }}>
              •
            </Typography>
            <Chip
              label={user?.roles[0]}
              size="small"
              variant="outlined"
              sx={{ color: "white", borderColor: "white" }}
            />
          </Stack>

          <IconButton color="inherit">
            <Notifications />
          </IconButton>

          {/* Language Switcher */}
          <LanguageSwitcher />

          {/* Dark Mode Toggle */}
          <ToggleColorMode mode={mode} toggleColorMode={toggleColorMode} />

          {user && (
            <Box sx={{ display: "flex", alignItems: "center", ml: 2 }}>
              {/* Avatar with Tooltip */}
              <Box sx={{ mr: -1 }}>
                <Avatar sx={{ bgcolor: "primary.light" }}>
                    <Person />
                </Avatar>
              </Box>
              <Button
                onClick={handleUserMenuOpen}
                sx={{ textTransform: "none", color: "inherit" }}
              >
                <Box
                  sx={{
                    display: { xs: "none", sm: "block" },
                    textAlign: "left",
                  }}
                >
                  <Typography
                    variant="body2"
                    sx={{ fontWeight: "medium", color: "white" }}
                  >
                    {user.username}
                  </Typography>
                </Box>
              </Button>
              <Menu
                anchorEl={userMenuAnchor}
                open={Boolean(userMenuAnchor)}
                onClose={handleUserMenuClose}
              >
                <MenuItem onClick={() => navigate("/profile")}>
                  <ListItemIcon>
                    <Person2 fontSize="small" />
                  </ListItemIcon>
                  <ListItemText>{t("menu.profile")}</ListItemText>
                </MenuItem>
                <MenuItem onClick={() => navigate("/change-password")}>
                  <ListItemIcon>
                    <Security fontSize="small" />
                  </ListItemIcon>
                  <ListItemText>{t("menu.changePassword")}</ListItemText>
                </MenuItem>
                <MenuItem onClick={handleLogout}>
                  <ListItemIcon>
                    <Logout fontSize="small" />
                  </ListItemIcon>
                  <ListItemText>{t("menu.logout")}</ListItemText>
                </MenuItem>
              </Menu>
            </Box>
          )}
        </Box>
      </Toolbar>
    </AppBar>
  );
};
const mapStateToProps = (state) => ({
  mode: state.config.mode,
  user: state.auth.credentials,
  auth: state.auth.authenticated,
  currentYear: state.auth.cyear,
});

const mapActionsToProps = {
  logoutUser,
};

export default connect(mapStateToProps, mapActionsToProps)(Header);
