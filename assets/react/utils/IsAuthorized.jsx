import React from "react";
import { connect } from "react-redux";
import { Navigate } from "react-router-dom";

const IsAuthorized = ({ roles, authRole, children }) => {
  return authRole && roles.some((role) => authRole.includes(role)) ? (
    children
  ) : (
    <Navigate to="/" />
  );
};

const mapStateToProps = (state) => ({
  roles: state.auth.credentials.roles,
});

export default connect(mapStateToProps)(IsAuthorized);
