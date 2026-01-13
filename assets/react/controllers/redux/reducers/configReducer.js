import { createSlice } from "@reduxjs/toolkit";

const initialConfig = {
  mode: "light",
  infoMessage: "",
  snackOpen: false,
  infoSeverity: "info",
};

const configSclice = createSlice({
  name: "config",
  initialState: initialConfig,
  reducers: {
    SET_MODE: (state, action) => {
      return {
        ...state,
        mode: state.mode === "light" ? "dark" : "light",
      };
    },
    SET_INFO_MESSAGE: (state, action) => {
      return {
        ...state,
        infoMessage: action.payload.message,
        snackOpen: true,
        infoSeverity: action.payload.severity,
      };
    },
    CLEAR_INFO_MESSAGE: (state, action) => {
      return {
        ...state,
        infoMessage: "",
        snackOpen: false,
        infoSeverity: "info",
      };
    },
  },
});

export const {
  SET_MODE,
  SET_INFO_MESSAGE,
  CLEAR_INFO_MESSAGE,
  REMOVE_MENU,
  ADD_MENU,
  CLEAR_MENU,
} = configSclice.actions;

export default configSclice.reducer;
