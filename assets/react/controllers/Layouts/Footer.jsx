import * as React from "react";
import IconButton from "@mui/material/IconButton";
import Link from "@mui/material/Link";
import Stack from "@mui/material/Stack";
import Typography from "@mui/material/Typography";

import FacebookIcon from "@mui/icons-material/FacebookOutlined";
import LinkedInIcon from "@mui/icons-material/LinkedIn";
import TwitterIcon from "@mui/icons-material/Twitter";
import { styled } from "@mui/material";
import CONSTANTS from "../../utils/Constants";


const drawerWidth = CONSTANTS.drawerWidth || 280; // Default to 280 if not defined in constants

function Copyright() {
  return (
    <Typography variant="body2" color="text.secondary" mt={1}>
      {"Copyright OPBco©"}
      <Link href="https://minesec.gov.cm/">MINESEC OFFICIAL&nbsp;</Link>
      {new Date().getFullYear()}
    </Typography>
  );
}

const Foot = styled('footer', { shouldForwardProp: (prop) => true })(
  ({ theme, open }) => ({
    paddingTop: theme.spacing(3),
    paddingInline:theme.spacing(3),
    marginTop: theme.spacing(8),
    display: "flex",
    justifyContent: "space-between",
    borderTop: "1px solid",
    borderColor: "divider",
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

export default function Footer({open}) {
  return (
    <Foot
      open={open}
    >
      <Copyright />
      <Stack
        direction="row"
        justifyContent="left"
        spacing={1}
        useFlexGap
        sx={{
          color: "text.secondary",
        }}
      >
        <IconButton
          color="inherit"
          href="https://facebook.com/minesec-courriel"
          aria-label="Facebook"
          sx={{ alignSelf: "center" }}
        >
          <FacebookIcon />
        </IconButton>
        <IconButton
          color="inherit"
          href="https://twitter.com/minesec-cmr"
          aria-label="Twit"
          sx={{ alignSelf: "center" }}
        >
          <TwitterIcon />
        </IconButton>
        <IconButton
          color="inherit"
          href="https://www.linkedin.com/gov/minesec-cmr/"
          aria-label="LinkedIn"
          sx={{ alignSelf: "center" }}
        >
          <LinkedInIcon />
        </IconButton>
      </Stack>
    </Foot>
  );
}
