@echo off
SCHTASKS /CREATE /SC HOURLY /TN "GreenVille\UpdateGameState" /TR "C:\xampp\php\php.exe C:\xampp\htdocs\greenville\includes\cron\update_game_state.php" /ST 00:00
echo Tâche cron créée avec succès
pause