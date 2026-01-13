import React from "react";
import { connect } from "react-redux";
import { Navigate, useLocation } from "react-router-dom";
import { setLastLocation } from "../controllers/redux/actions/UserActions";

const IsAuthenticated = ({ authenticated, auth, lastLocation, setLastLocation, children }) => {
  const location = useLocation();

  if (authenticated !== auth && auth === true) {
    setLastLocation(location.pathname);
  }

  return authenticated === auth ? (
    children
  ) : auth === true ? (
    <Navigate to="/login" replace />
  ) : (
    <Navigate to={lastLocation} />
  );
};

const mapStateToProps = (state) => ({
  authenticated: state.auth.authenticated,
  lastLocation: state.auth.lastLocation,
});

const mapActionsToProps = {
  setLastLocation
};

export default connect(mapStateToProps, mapActionsToProps)(IsAuthenticated);
