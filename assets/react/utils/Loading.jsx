import React from "react";
import { CircularProgress, Box } from "@mui/material";
import styled from "@emotion/styled";


const DisabledBackground = styled(Box)({
    width: "100%",
    height: "100%",
    position: "relative",
    background: "#ccc",
    opacity: 0.5,
    zIndex: 1
  });
  
const Loading = () => (
    <>
      <CircularProgress
        size={70}
        sx={{
          position: "fixed",
          left: "50%",
          top: "50%",
          transform: "translate(-50%, -50%)",
          zIndex: 2
        }}
      />
      <DisabledBackground />
    </>
  );

export default Loading;