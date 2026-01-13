import { createSlice } from "@reduxjs/toolkit";

const initialState = {
  authenticated: false,
  loading: false,
  errors: null,
  roles: [],
  credentials: null,
  user_details: null,
  lastLocation: '/',
};

const UserSclice = createSlice({
  name: "user",
  initialState,
  reducers: {
    AUTHENTICATED: (state, action) => {
      return {
        ...state,
        errors: "",
        authenticated: true,
        loading: false,
      };
    },
    IS_BUSY: (state, action) => {
      return {
        ...state,
        loading: true,
      };
    },
    SET_ERRORS: (state, action) => {
      return {
        ...state,
        errors: action.payload,
        loading: false,
      };
    },
    CLEAR_ERRORS: (state, action) => {
      return {
        ...state,
        errors: "",
        loading: false,
      };
    },
    UNAUTHENTICATED: (state, action) => {
      return { ...initialState};
    },
    SET_LAST_LOCATION: (state, action) => {
      return {
        ...state,
        lastLocation: action.payload,
      };
    },
    SET_USER: (state, action) => {
      return {
        ...state,
        credentials: action.payload.user,
        roles: action.payload.user.roles,
        errors: "",
        authenticated: true,
        loading: false, 
      };
    },
  },
});

export const {
  AUTHENTICATED,
  SET_ERRORS,
  CLEAR_ERRORS,
  SET_USER,
  UNAUTHENTICATED,
  IS_BUSY,
  SET_LAST_LOCATION
} = UserSclice.actions;

export default UserSclice.reducer;
