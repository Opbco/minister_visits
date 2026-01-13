import {
  AUTHENTICATED,
  SET_LAST_LOCATION,
  SET_ERRORS,
  CLEAR_ERRORS,
  UNAUTHENTICATED,
  IS_BUSY,
  SET_USER,
} from "../reducers/UserReducer";
import axios from "axios";
import CONSTANTS from "../../../utils/Constants";

const publicAxios = axios.create({
  baseURL: CONSTANTS.BASE_URL,
  headers: {
    "Content-Type": "application/json",
    
  },
});

export const loginUser = (userData) => (dispatch) => {
  dispatch(IS_BUSY());
  publicAxios
    .post("/api/login_check", userData)
    .then(async (res) => {
      if (res.status === 204) {
        await dispatch(getUserData()); // Fetch user data after successful login
      }
    })
    .catch((err) => {
      dispatch(SET_ERRORS(err.response?.data?.message));
    });
};

export const registerUser = (userData, navigate) => (dispatch) => {
  dispatch(IS_BUSY());
  publicAxios
    .post("/api/register", userData)
    .then((res) => {
      if (res.status === 201) {
        dispatch(
          loginUser(
            { username: userData.username, password: userData.plainPassword },
            navigate,
            "/"
          )
        );
      } else {
        dispatch(SET_ERRORS(res.data.message));
      }
    })
    .catch((err) => {
      dispatch(SET_ERRORS(err.message));
    });
};

export const logoutUser = () => (dispatch) => {
  publicAxios
    .post("/api/logout", {
      headers: {
        withCredentials: true,
      },
    })
    .then((res) => {
      dispatch(UNAUTHENTICATED());
    })
    .catch((err) => {
      dispatch(SET_ERRORS(err.message));
    });
};

export const setErrors = (errors) => (dispatch) => {
  dispatch(SET_ERRORS(errors));
};

export const setLastLocation = (location) => (dispatch) => {
  dispatch(SET_LAST_LOCATION(location));
};

export const getUserData = () => (dispatch) => {
  publicAxios
    .get("/api/me", {
      headers: {
        "Content-Type": "application/json",
        withCredentials: true,
      },
    })
    .then((res) => {
      dispatch(SET_USER(res.data));
    })
    .catch((err) => {
      console.log(err);
      dispatch(SET_ERRORS(err.message));
    });
};

export const clearErrors = () => (dispatch) => {
  dispatch(CLEAR_ERRORS());
};

export const loading = () => (dispatch) => {
  dispatch(IS_BUSY());
};
