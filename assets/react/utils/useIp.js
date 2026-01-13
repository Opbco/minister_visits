import React, { useEffect, useState } from "react";

export default useIp = () => {
  const [ip, setIp] = useState("");

  useEffect(() => {
    (async function noname() {
      const resp = await fetch("https://api.ipify.org/?format=json");
      const data = await resp.json();
      setIp(data.ip)})();
  }, []);

  return { ip };
};