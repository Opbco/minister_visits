import React, { useEffect } from "react";
import {
  Box,
  Typography,
} from "@mui/material";
import { useTranslation } from "react-i18next";
import { connect } from "react-redux";
import { useHasRole } from "../../utils/useHasRole";
import MyReunions from "../Components/MyReunions";

const Dashboard = ({ user }) => {
  const { t } = useTranslation();
  const hasRole = useHasRole();

  useEffect(() => {
    document.title = t("dashboard.title");
  }, [t]);



  return (
    <Box sx={{ p: 3 }}>
      <Box
        sx={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          mb: 3,
        }}
      >
        <Typography variant="h4" component="h1" sx={{ fontWeight: "bold" }}>
          {t("dashboard.title")}
        </Typography>
        <Typography variant="body2" color="text.secondary">
          {t("dashboard.lastUpdated", {
            date: new Date(),
            formatParams: {
              date: {
                year: "numeric",
                month: "long",
                day: "numeric",
              },
            },
          })}
        </Typography>
      </Box>
      <MyReunions />
    </Box>
  );
};

const mapStateToProps = (state) => ({
  user: state.auth.credentials,
});

const mapActionsToProps = {};

export default connect(mapStateToProps, mapActionsToProps)(Dashboard);
