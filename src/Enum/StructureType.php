<?php

namespace App\Enum;

enum StructureType: string
{
    case CABINET = 'cabinet';
    case SESEEN = 'seseen';
    case SG = 'sg';
    case IGE = 'ige';
    case INSPECTION_NATIONAL = 'inspection nationale';
    case INSPECTION_REGIONAL = 'inspection régionale';
    case IGS = 'igs';
    case DIRECTION = 'direction';
    case ETABLISSEMENT = 'etablissement';
    case SERVICE = 'service';
    case SOUS_DIRECTION = 'sous-direction';
    case CELLULE = 'cellule';
    case BRIGADE = 'brigade';
    case SDAG = 'sdag';
    case BUREAU = 'bureau';
    case DIVISION = 'division';
    case INSPECTION_SERVICE = 'inspection de services';
    case ICG = 'icg';
    case SP = 'sp';
    case CT = 'ct';
    case ENI = 'ecole normale d\'instituteurs';

    public function label(): string
    {
        return match($this) {
            self::CABINET => 'Cabinet du Ministre',
            self::SESEEN => 'Sécrétariat d’Etat en charge de l\'Enseignement Normale',
            self::SG => 'Sécrétariat Général',
            self::IGE => 'Inspection Générale des Enseignements',
            self::INSPECTION_NATIONAL => 'Inspection Nationale',
            self::INSPECTION_REGIONAL => 'Inspection Régionale',
            self::IGS => 'Inspection Générale des Services',
            self::DIRECTION => 'Direction',
            self::ETABLISSEMENT => 'Etablissement',
            self::SERVICE => 'Service',
            self::SOUS_DIRECTION => 'Sous-Direction',
            self::CELLULE => 'Cellule',
            self::BRIGADE => 'Brigade',
            self::SDAG => 'Sous-Direction des Affaires Générales',
            self::BUREAU => 'Bureau',
            self::DIVISION => 'Division',
            self::INSPECTION_SERVICE => 'Inspection de Services',
            self::ICG => 'Inspection de Coordination Générale',
            self::SP => 'Sécrétariat Particulier',
            self::CT => 'Conseil Technique',
            self::ENI => 'Ecole Normale d\'Instituteurs',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::CABINET => 'Minister\'s Office',
            self::SESEEN => 'State Secretariat in charge of Normal Education',
            self::SG => 'General Secretariat',
            self::IGE => 'General Inspection of Education',
            self::INSPECTION_NATIONAL => 'National Inspection',
            self::INSPECTION_REGIONAL => 'Regional Inspection',
            self::IGS => 'General Inspection of Services',
            self::DIRECTION => 'Directorate',
            self::ETABLISSEMENT => 'Schools',
            self::SERVICE => 'Service',
            self::SOUS_DIRECTION => 'Sub-Directorate',
            self::CELLULE => 'Unit',
            self::BRIGADE => 'Brigade',
            self::SDAG => 'Sub-Directorate of General Affairs',
            self::BUREAU => 'Office',
            self::DIVISION => 'Division',
            self::INSPECTION_SERVICE => 'Inspection of Services',
            self::ICG => 'General Coordination Inspection',
            self::SP => 'Private Secretariat',
            self::CT => 'Technical Advisor',
            self::ENI => 'Teacher Training College',
        };
    }

}