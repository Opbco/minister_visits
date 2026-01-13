import { useState, useEffect } from 'react';

const IdleTimer = ({ onIdle, idleTime = 5 }) => {
  useEffect(() => {
    let idleTimeout;

    const resetTimer = () => {
      clearTimeout(idleTimeout);
      idleTimeout = setTimeout(onIdle, idleTime * 60 * 1000); // Convert minutes to ms
    };

    // Reset timer on user activity
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
    events.forEach(event => document.addEventListener(event, resetTimer));

    resetTimer();

    return () => {
      clearTimeout(idleTimeout);
      events.forEach(event => document.removeEventListener(event, resetTimer));
    };
  }, [onIdle, idleTime]);

  return null;
};

export default IdleTimer;