import axios from "axios";
import CONSTANTS from "../../../utils/Constants";

const publicAxios = axios.create({
  baseURL: CONSTANTS.BASE_API_URL,
  headers: {
    "Content-Type": "application/json",
  },
});

export default publicAxios;
