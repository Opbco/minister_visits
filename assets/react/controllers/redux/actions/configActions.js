import {
  SET_MODE,
  SET_INFO_MESSAGE,
  CLEAR_INFO_MESSAGE,
} from "../reducers/configReducer";

export const changeMode = () => (dispatch) => {
  dispatch(SET_MODE());
};

export const setInfoMsg = (message) => (dispatch) => {
  dispatch(SET_INFO_MESSAGE(message));
};

export const clearInfoMsg = () => (dispatch) => {
  dispatch(CLEAR_INFO_MESSAGE());
};
