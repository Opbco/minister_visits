import { useSelector } from "react-redux";

export const useHasRole = () => {
  const userRoles = useSelector((state) => state.auth.credentials.roles);

  const normalizeRole = (role) => `ROLE_ADMIN_${role.replace(/:/g, '_').toUpperCase()}`;
  
  return (role) => userRoles && userRoles.includes(normalizeRole(role)) || userRoles.includes(role);
};
