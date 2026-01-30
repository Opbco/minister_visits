import React from "react";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import "./../utils/i18n";
import { Provider } from "react-redux";
import { persistor, store } from "./redux/store";
import { PersistGate } from "redux-persist/integration/react";
import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import Dashboard from "./Pages/Dashboard";
import MainLayout from "./Layouts/MainLayout";
import ErrorPage from "./Pages/ErrorPage";
import Loading from "../utils/Loading";
import SignIn from "./Pages/SignIn";
import IsAuthenticated from "./../utils/IsAuthenticated";
import IsAuthorized from "./../utils/IsAuthorized";
import MeetingDetails from "./Pages/MeetingDetails";

const Main = ({ page }) => {
  const { t } = useTranslation();

  const router = createBrowserRouter([
    {
      path: "/",
      element: (
        <IsAuthenticated auth={true}>
          <MainLayout />
        </IsAuthenticated>
      ),
      errorElement: <ErrorPage />,
      children: [
        {
          index: true,
          element: <Dashboard title="MinistrySuiv-OPBco" />,
          handle: {
            crumb: () => <Link to="/">{t("menu.dashboard")}</Link>,
          },
        },
        {
          path: "reunions/:reunionId",
          element: <MeetingDetails title="MinistrySuiv-OPBco" />,
          handle: {
            crumb: () => <Link to="/">{t("menu.dashboard")}</Link>,
          },
        },
      ],
    },
    {
      path: "login",
      element: (
        <IsAuthenticated auth={false}>
          <SignIn title="let's log in" />
        </IsAuthenticated>
      ),
      errorElement: <ErrorPage />,
      handle: {
        crumb: () => <Link to="/login">{t("login")}</Link>,
      },
    },
  ]);

  return (
    <React.StrictMode>
      <Provider store={store}>
        <PersistGate persistor={persistor} loading={<Loading />}>
          <RouterProvider router={router} />
        </PersistGate>
      </Provider>
    </React.StrictMode>
  );
};

export default Main;
