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

export const bloodGroups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
export const commonAllergies = [
  "Arachides",
  "Fruits à coque",
  "Lait",
  "Œufs",
  "Poisson",
  "Crustacés",
  "Soja",
  "Blé",
  "Gluten",
  "Pollen",
  "Acariens",
  "Poils d'animaux",
  "Médicaments",
  "Latex",
  "Insectes",
  "Fruits exotiques",
];

export const isDateValid = (dateStr) => !isNaN(new Date(dateStr));


export function calculateSolde(eleve){
   return mergeUnique(
          eleve.fraisCourant,
          eleve.fraisImpayes
        ).reduce((s, c)=> s + (parseFloat(c.montantApresRemise) - parseFloat(c.montantDejaPaye)), 0);
  
}

export function situationTranches(eleve){
   const pensionFee = eleve.fraisCourant.filter((f) => f.structureFrais.typeFrais.pension)[0];
   const allTranches = pensionFee?.structureFrais.tranches;
   const montantPaye = pensionFee?.montantDejaPaye;
   const orderedTranches = [...allTranches].sort((a, b) => a.ordreTranche - b.ordreTranche);
   let resumedTranches = orderedTranches?.reduce(function(r, c){
        const trancheStatus = getTrancheStatus(
                                c, 
                                allTranches, 
                                montantPaye
                              );
        return [{ordre: c.ordreTranche, nom: c.nomTranche, dateLine: c.dateLimite, ...trancheStatus }, ...r ];
   }, []);

   return resumedTranches.sort((a, b) => a.ordre - b.ordre);
}

export function formatCurrency(amount) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(amount);
}

export function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
}

export function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

export function calculateAge(dateNaissance) {
  const today = new Date();
  const birthDate = new Date(dateNaissance);
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();
  
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  
  return age;
}

/**
 * Returns a color name based on the payment mode
 * @param {string} mode Payment mode
 * @returns {string} Color name
 */
export function getPaymentModeColor(mode){
    switch (mode) {
      case 'Espèces': return 'success';
      case 'Mobile Money': return 'primary';
      case 'Virement Bancaire': return 'info';
      case 'Chèque': return 'warning';
      default: return 'default';
    }
  };

export function getDepenseStatutColor(status){
    switch (status) {
      case 'payee': return 'success';
      case 'en_attente': return 'primary';
      case 'validee': return 'info';
      default: return 'error';
    }
  };

  /**
   * Generates a unique 10-character string starting with the academic year code.
   * Academic year starts in August and ends in July next year.
   * @param {string} fullName
   * @param {string} dob ISO date string
   * @param {string} placeOfBirth
   * @returns {string}
   */
  export function generateUniqueMatricule(student) {
    // Determine academic year code (e.g., '2425' for 2024-2025)
    console.log(student);
    const now = new Date();
    const year = now.getFullYear() % 100;
    const month = now.getMonth(); // 0 = Jan, 7 = Aug
    const startYear = month >= 7 ? year : year - 1;
    const endYear = (startYear + 1) % 100;
    const academicYearCode = `${startYear.toString().padStart(2, '0')}${endYear.toString().padStart(2, '0')}`;

    // Simple hash function for demonstration
    const input = `${student.nom}${student.prenom}|${student.dateNaiss}|${student.lieuNaiss}`;
    let hash = 0;
    for (let i = 0; i < input.length; i++) {
      hash = ((hash << 5) - hash) + input.charCodeAt(i);
      hash |= 0; // Convert to 32bit integer
    }
    // Convert hash to base36 and pad to 6 chars
    const hashStr = Math.abs(hash).toString(36).padStart(6, '0').slice(0, 6);
    return `${academicYearCode}${hashStr}`.toUpperCase().slice(0, 10);
  }

export function getUniqueByProperty(arr, property) {
  const seen = new Map();
  return arr.filter(item => {
    const key = item[property];
    if (seen.has(key)) {
      return false;
    }
    seen.set(key, true);
    return true;
  });
}

export function getStatusColor(status){
  switch (status) {
    case "confirmee":
      return "success";
    case "provisoire":
      return "warning";
    case "transferee":
      return "info";
    default:
      return "error";
  }
};


// Function to check if a tranche is overdue
export function isTrancheOverdue (tranche, allTranches, totalPaid){
    const currentDate = new Date();
    const dueDate = new Date(tranche.dateLimite);
    
    // If due date hasn't passed, it's not overdue
    if (currentDate <= dueDate) return false;
    
    // Calculate sum of all tranches that should be paid by this tranche's due date
    const tranchesToBePaid = allTranches
      .filter(t => t.ordreTranche <= tranche.ordreTranche)
      .reduce((sum, t) => sum + parseFloat(t.montantTranche), 0);
    
    // Check if total paid is less than what should have been paid
    return totalPaid < tranchesToBePaid;
  };

  // Function to get tranche status and balance
 export function getTrancheStatus (tranche, allTranches, totalPaid){
    const trancheAmount = parseFloat(tranche.montantTranche);
    const sumPreviousTranches = allTranches
      .filter(t => t.ordreTranche < tranche.ordreTranche)
      .reduce((sum, t) => sum + parseFloat(t.montantTranche), 0);
    
    const paidForThisTranche = Math.max(0, totalPaid - sumPreviousTranches);
    const trancheBalance = Math.max(0, trancheAmount - paidForThisTranche);
    
    if (paidForThisTranche >= trancheAmount) {
      return { status: 'paid', color: 'success.main', balance: 0, paidAmount: trancheAmount };
    } else if (isTrancheOverdue(tranche, allTranches, totalPaid)) {
      return { status: 'overdue', color: 'error.main', balance: trancheBalance, paidAmount: paidForThisTranche };
    } else {
      return { status: 'pending', color: 'warning.main', balance: trancheBalance, paidAmount: paidForThisTranche };
    }
  };

export function mergeUnique(arr1, arr2, key = 'id') {
  const map = new Map();
  [...arr1, ...arr2].forEach(obj => map.set(obj[key], obj));
  return Array.from(map.values());
}
/**
 * Generates a random hex color string
 * @returns {string} A random color string, e.g. #3fa9f5
 */

export function getRandomColor(){
  const letters = '0123456789ABCDEF';
  let color = '#';
  for (let i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
};

export function getStatutPaiementColor(solde) {
  if (solde === 0) return 'success';
  if (solde > 0) return 'error';
  return 'warning';
}

export function getStatutPaiementText(solde) {
  if (solde === 0) return 'Soldé';
  if (solde > 0) return 'En cours';
  return 'Trop perçu';
}


export function getTotalSpentByCategory(expenses){
  if (!Array.isArray(expenses)) {
    console.error("Invalid input: The provided data is not an array.");
    return {};
  }

  return expenses.reduce((totals, expense) => {
    // Ensure the necessary properties exist to avoid errors
    if (expense && expense.categorieDepense && expense.categorieDepense?.nom && expense.montant) {
      const { nom } = expense.categorieDepense;
      const { montant } = expense;
      
      // Add the expense amount to the correct fee type total
      totals[nom] = (totals[nom] || 0) + ((expense.statut == "payee" || expense.statut == "validee") ? parseFloat(montant) : 0);
    }
    return totals;
  }, {});
};

export function getTotalPaymentsByFeeType(payments){
  if (!Array.isArray(payments)) {
    console.error("Invalid input: The provided data is not an array.");
    return {};
  }

  return payments.reduce((totals, payment) => {
    // Ensure the necessary properties exist to avoid errors
    if (payment && payment.typeFrais && payment.typeFrais.nomFrais && payment.montantPaye) {
      const { nomFrais } = payment.typeFrais;
      const { montantPaye } = payment;
      
      // Add the payment amount to the correct fee type total
      totals[nomFrais] = (totals[nomFrais] || 0) + (payment.isCancelled ? 0 : parseFloat(montantPaye));
     montantPaye;
    }
    return totals;
  }, {});
};

export function getInscriptionsByEtablissement(apiResponse, etablissementId) {
  if (!apiResponse.inscriptions || !Array.isArray(apiResponse.inscriptions)) {
    return [];
  }
  
  const inscriptions = apiResponse.inscriptions.filter(inscription => {
    // Check if the inscription's salleClasse has an etablissement with matching ID
    return inscription.salleClasse && 
           inscription.salleClasse.etablissement && 
           inscription.salleClasse.etablissement.id === etablissementId;
  });

  return inscriptions?.sort((a, b)=> b.anneeScolaire.id - a.anneeScolaire.id);
}

// Function to extract all payments from specific inscriptions
export function getPaiementsFromInscriptions(inscriptions) {
  const allPaiements = [];
  
  inscriptions.forEach(inscription => {
    if (inscription.inscriptionDetailFrais && Array.isArray(inscription.inscriptionDetailFrais)) {
      inscription.inscriptionDetailFrais.forEach(frais => {
        if (frais.paiements && Array.isArray(frais.paiements)) {
          frais.paiements.forEach(paiement => {
            allPaiements.push({
              ...paiement,
              // Additional metadata for context
              inscriptionId: inscription.id,
              anneeScolaireId: inscription.anneeScolaire.id,
              anneeScolaireNom: inscription.anneeScolaire.name,
              typeFrais: frais.structureFrais.typeFrais.nomFrais,
              isFacultatif: frais.exempte,
              structureFraisId: frais.structureFrais.id,
            });
          });
        }
      });
    }
  });
  
  return allPaiements.sort((a, b) => b.anneeScolaireId - a.anneeScolaireId || b.datePaiement - a.datePaiement);
}

export function getAllPaiementsForInscription(inscription){
  const inscriptions = [inscription];
  return getPaiementsFromInscriptions(inscriptions);
}

export function getPaiementsByEtablissement(apiResponse, etablissementId) {
  const inscriptions = getInscriptionsByEtablissement(apiResponse, etablissementId);
  return getPaiementsFromInscriptions(inscriptions);
}

export function totalPaidPerYear(paiements, year) {
  return paiements
    .filter(p => p.anneeScolaireId === year && !p.isCancelled)
    .reduce((total, p) => total + (p.anneeScolaireId === year && !p.isCancelled) ? parseFloat(p.montantPaye): 0, 0);
}


export function getAllStructureFraisDetails(inscription)
{
  if (!inscription || !inscription.inscriptionDetailFrais || !Array.isArray(inscription.inscriptionDetailFrais)) {
    return [];
  }
  
  return inscription.inscriptionDetailFrais.map(frais => {
    const activePaiements = frais.paiements?.filter(p => !p.isCancelled) || [];
    const cancelledPaiements = frais.paiements?.filter(p => p.isCancelled) || [];
    
    const totalPaid = activePaiements.reduce((sum, p) => sum + parseFloat(p.montantPaye || 0), 0);
    const totalCancelled = cancelledPaiements.reduce((sum, p) => sum + parseFloat(p.montantPaye || 0), 0);
    const totalRequired = parseFloat(frais.montantApresRemise || 0);
    const remainingBalance = totalRequired - totalPaid;
    const progressPercentage = (totalPaid / totalRequired) * 100;
    
    return {
      structureFrais: {
        id: frais.structureFrais.id,
        typeFrais: frais.structureFrais.typeFrais.nomFrais,
        montantRequis: totalRequired,
        montantRemise: totalRequired - parseFloat(frais.montantInitial || 0),
        facultatif: frais.exempte
      },
      totalPaid,
      totalCancelled,
      remainingBalance,
      progressPercentage: isNaN(progressPercentage) ? 0 : Math.min(progressPercentage, 100),
      isFullyPaid: remainingBalance <= 0,
      payments: activePaiements,
      cancelledPaiements,
      allPaiements: frais.paiements || []
    };
  });
}


export function getPaymentDetailsForStructureFrais(inscription, structureFraisId) {
  if (!inscription || !inscription.allStructureFraisCurrent || !Array.isArray(inscription.allStructureFraisCurrent)) {
    return null;
  }
  
  const structureFrais = inscription.allStructureFraisCurrent.find(
    structure => structure.id === structureFraisId
  );
  
  if (!structureFrais) {
    return null;
  }
  
  const activePaiements = structureFrais.paiements?.filter(p => !p.isCancelled) || [];
  const cancelledPaiements = structureFrais.paiements?.filter(p => p.isCancelled) || [];
  
  const totalPaid = activePaiements.reduce((sum, p) => sum + parseFloat(p.montantPaye || 0), 0);
  const totalCancelled = cancelledPaiements.reduce((sum, p) => sum + parseFloat(p.montantPaye || 0), 0);
  const totalRequired = parseFloat(structureFrais.montant || 0);
  const remainingBalance = totalRequired - totalPaid;
  
  return {
    structureFrais: {
      id: structureFrais.id,
      typeFrais: structureFrais.typeFrais.nomFrais,
      montantRequis: totalRequired,
      facultatif: structureFrais.facultatif
    },
    totalPaid,
    totalCancelled,
    remainingBalance,
    isFullyPaid: remainingBalance <= 0,
    activePaiements,
    cancelledPaiements,
    allPaiements: structureFrais.paiements || []
  };
}

export function getPaymentProgress(paid, total){
    return (parseFloat(paid) / parseFloat(total)) * 100;
  };

export function getStatusInfo (balance){
    const balanceAmount = parseFloat(balance);
    if (balanceAmount === 0) {
      return {
        color: 'bg-green-100 text-green-800',
        text: 'Payé',
        icon: CheckCircle,
        iconColor: 'text-green-600'
      };
    }
    return {
      color: 'bg-red-100 text-red-800',
      text: 'En cours',
      icon: AlertCircle,
      iconColor: 'text-red-600'
    };
  };

/**
 * Returns an emoji string based on the percentage of the score against the max score.
 * * @param {number | null | undefined} score The score obtained.
 * @param {number} maxScore The maximum possible score.
 * @returns {string} The corresponding emoji.
 */
export function getEmojiOrScore(score, isMaternelle=false, maxScore=20){

    if (score === null || score === undefined || maxScore === 0) {
        return ''; 
    }

    if(!isMaternelle) return score;

    const percentage = (score / maxScore) * 100;

    if (percentage <= 25) return '😡';
    if (percentage < 50) return '😐';
    if (percentage < 75) return '🙂';
    return '😍';
};
