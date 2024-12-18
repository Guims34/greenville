<?php
class ExperienceManager {
    public static function addExperience($userId, $amount) {
        global $db;
        try {
            $db->beginTransaction();
            
            // Code existant pour addExperience...
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Erreur lors de l'ajout d'expérience : " . $e->getMessage());
            return false;
        }
    }
}
