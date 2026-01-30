import { intervalToDuration } from "date-fns";

export const daysBefore = (days) => {
  const date = new Date();
  date.setDate(date.getDate() - days);
  return date;
};

export const yearsBefore = (years) => {
  const date = new Date();
  date.setFullYear(date.getFullYear() - years);
  return date;
};

export const formatTimeToHHMM = (isoTimestamp) => {
  // Create a new Date object from the ISO 8601 string
  const date = new Date(isoTimestamp);
  
  // Get the hours and minutes from the Date object
  const hours = date.getHours();
  const minutes = date.getMinutes();
  
  // Pad with a leading zero if the hours or minutes are a single digit
  const formattedHours = hours < 10 ? `0${hours}` : hours;
  const formattedMinutes = minutes < 10 ? `0${minutes}` : minutes;
  
  // Return the time in "HH:MM" format
  return `${formattedHours}:${formattedMinutes}`;
};

export const getAge = (dob) => {
  let duration = intervalToDuration({
    start: dob,
    end: new Date(),
  });

  return `${duration.years} an(s), ${duration.months} mois, ${duration.days} jour(s)`;
};

// --- UTILS & CONSTANTS ---
export const formatDate = (dateStr) => {
    if (!dateStr) return "-";
    return new Intl.DateTimeFormat('fr-FR', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    }).format(new Date(dateStr));
};

export const getStatusColor = (status) => {
    const map = {
        'planifie': 'info',
        'en_cours': 'primary',
        'tenue': 'success',
        'reporte': 'warning',
        'annulee': 'error',
        'completed': 'success',
        'pending': 'default',
        'in_progress': 'primary',
        'cancelled': 'error'
    };
    return map[status] || 'default';
};

export const getStatusConfig = (statusId, t) => {
  switch (statusId) {
    case 1:
    return { label: t("planned"), color: "info" };
    case 2:
    return { label: t("confirmed"), color: "secondary" };
    case 3:
    return { label: t("in_progress"), color: "primary" };
    case 4:
    return { label: t("completed"), color: "success" };
    case 5:
    return { label: t("cancelled"), color: "error" };
    case 6:
    return { label: t("postponed"), color: "warning" };
    default:
    return { label: `Statut ${statusId}`, color: "default" };
  }
};

export const getStatusAction = (status, t) => {
  switch (status) {
    case 'pending':
      return { label: t("pending"), color: "warning" };
    case 'in_progress':
      return { label: t("in_progress"), color: "primary" };
    case 'completed':
      return { label: t("completed"), color: "success" };
    case 'cancelled':
      return { label: t("cancelled"), color: "error" };
    default:
      return { label: `Statut ${statusId}`, color: "default" };
  }
};