import { createApi, fetchBaseQuery } from "@reduxjs/toolkit/query/react";
import CONSTANTS from "../../../utils/Constants";
import { UNAUTHENTICATED } from "../reducers/UserReducer";

const baseQuery = fetchBaseQuery({
  baseUrl: CONSTANTS.BASE_API_URL,
  prepareHeaders: (headers, { getState, endpoint }) => {
    if (endpoint.startsWith("upload")) {
      headers.set("Accept", "application/json");
    } else if (!endpoint.startsWith("form")) {
      headers.set("Content-Type", "application/json");
    }

    headers.set("withCredentials", "true");
    return headers;
  },
});

const baseQueryWithReauth = async (args, api, extraOptions) => {
  let result = await baseQuery(args, api, extraOptions);
  if (result.error && result.error.status === 401) {
    // log out the user
    api.dispatch(UNAUTHENTICATED());
  }
  return result;
};

export const openApi = createApi({
  reducerPath: "openApi",
  baseQuery: baseQueryWithReauth,
  tagTypes: [
    "Evenements",
  ],
  endpoints: (builder) => ({
    uploadDocument: builder.mutation({
      query: (arg) => ({
        url: `/documents`,
        method: "POST",
        body: arg.data,
        formData: true,
      }),
      transformResponse: (response) => JSON.parse(response),
    }),
  }),
});

export const {
  useUploadDocumentMutation,
} = openApi;