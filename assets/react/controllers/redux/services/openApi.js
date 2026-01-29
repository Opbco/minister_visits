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
    "Evenement",
    "Reunion",
    "Visite"
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

    // ==================== REUNION ACCESS ENDPOINTS ====================
    
    /**
     * Get reunions accessible to the current authenticated user
     * @returns {Array} List of accessible reunions
     */
    getMyAccessibleReunions: builder.query({
      query: () => ({
        url: `/reunions/accessible/me`,
        method: "GET",
      }),
      providesTags: (result) =>
        result
          ? [
              ...result.map(({ id }) => ({ type: "Reunion", id })),
              { type: "Reunion", id: "LIST" },
            ]
          : [{ type: "Reunion", id: "LIST" }],
    }),

    /**
     * Get reunions accessible to a user by their user account ID
     * @param {number} userId - User account ID
     * @returns {Array} List of accessible reunions
     */
    getAccessibleReunionsByUserId: builder.query({
      query: (userId) => ({
        url: `/reunions/accessible/user/${userId}`,
        method: "GET",
      }),
      providesTags: (result) =>
        result
          ? [
              ...result.map(({ id }) => ({ type: "Reunion", id })),
              { type: "Reunion", id: "LIST" },
            ]
          : [{ type: "Reunion", id: "LIST" }],
    }),

    /**
     * Get reunions accessible to a personnel by their personnel ID
     * @param {number} personnelId - Personnel ID
     * @returns {Array} List of accessible reunions
     */
    getAccessibleReunionsByPersonnelId: builder.query({
      query: (personnelId) => ({
        url: `/reunions/accessible/personnel/${personnelId}`,
        method: "GET",
      }),
      providesTags: (result) =>
        result
          ? [
              ...result.map(({ id }) => ({ type: "Reunion", id })),
              { type: "Reunion", id: "LIST" },
            ]
          : [{ type: "Reunion", id: "LIST" }],
    }),

    /**
     * Get filtered accessible reunions for a user
     * @param {Object} params - Filter parameters
     * @param {number} params.userId - User account ID
     * @param {string} params.startDate - Start date filter (YYYY-MM-DD)
     * @param {string} params.endDate - End date filter (YYYY-MM-DD)
     * @param {number} params.status - Reunion status filter
     * @param {number} params.limit - Maximum number of results
     * @returns {Array} List of filtered reunions
     */
    getFilteredAccessibleReunions: builder.query({
      query: ({ userId, startDate, endDate, status, limit }) => {
        const params = new URLSearchParams();
        if (startDate) params.append("startDate", startDate);
        if (endDate) params.append("endDate", endDate);
        if (status !== undefined) params.append("status", status);
        if (limit) params.append("limit", limit);

        return {
          url: `/reunions/accessible/user/${userId}/filtered?${params.toString()}`,
          method: "GET",
        };
      },
      providesTags: (result) =>
        result
          ? [
              ...result.map(({ id }) => ({ type: "Reunion", id })),
              { type: "Reunion", id: "FILTERED" },
            ]
          : [{ type: "Reunion", id: "FILTERED" }],
    }),

    /**
     * Get reunion statistics for a user
     * @param {number} userId - User account ID
     * @returns {Object} Reunion statistics including total, upcoming, past, and by status
     */
    getReunionStatsByUserId: builder.query({
      query: (userId) => ({
        url: `/reunions/accessible/user/${userId}/stats`,
        method: "GET",
      }),
      providesTags: [{ type: "Reunion", id: "STATS" }],
    }),

    /**
     * Get reunions where the user is a direct participant
     * @param {number} userId - User account ID
     * @returns {Array} List of reunions where user is a direct participant
     */
    getDirectParticipationsByUserId: builder.query({
      query: (userId) => ({
        url: `/reunions/participations/user/${userId}`,
        method: "GET",
      }),
      providesTags: (result) =>
        result
          ? [
              ...result.map(({ id }) => ({ type: "Reunion", id })),
              { type: "Reunion", id: "PARTICIPATIONS" },
            ]
          : [{ type: "Reunion", id: "PARTICIPATIONS" }],
    }),
  }),
});

export const {
  useUploadDocumentMutation,
  useGetMyAccessibleReunionsQuery,
  useGetAccessibleReunionsByUserIdQuery,
  useGetAccessibleReunionsByPersonnelIdQuery,
  useGetFilteredAccessibleReunionsQuery,
  useGetReunionStatsByUserIdQuery,
  useGetDirectParticipationsByUserIdQuery,
} = openApi;