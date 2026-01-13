import { useEffect } from "react";
import { useLocation } from "react-router-dom";

// ----------------------------------------------------------------------

export default function ScrollToContent() {
  const { pathname } = useLocation();

  useEffect(() => {
    if (pathname !== "/") {
      window.scrollTo(0, 200);
    } else {
      window.scrollTo(0, 0);
    }
  }, [pathname]);

  return null;
}