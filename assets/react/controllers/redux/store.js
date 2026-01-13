import { combineReducers, configureStore } from "@reduxjs/toolkit";
import {
  persistReducer,
  FLUSH,
  REHYDRATE,
  PAUSE,
  PERSIST,
  PURGE,
  REGISTER,
  persistStore,
} from "redux-persist";
import UserReducer from "./reducers/UserReducer";
import configReducer from "./reducers/configReducer";
import { openApi } from "./services/openApi";
import sessionStorage from "redux-persist/es/storage/session";

const persistConfig = {
  key: "root",
  version: 1,
  storage: sessionStorage,
  blacklist: [openApi.reducerPath],
};

const rootReducer = combineReducers({
  auth: UserReducer,
  config: configReducer,
  [openApi.reducerPath]: openApi.reducer,
});

const persistedReducer = persistReducer(persistConfig, rootReducer);

const store = configureStore({
  reducer: persistedReducer,
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: [FLUSH, REHYDRATE, PAUSE, PERSIST, PURGE, REGISTER],
      },
    }).concat(openApi.middleware),
});

const persistor = persistStore(store);

export { store, persistor };