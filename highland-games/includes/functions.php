<?php
/**
 * Common functions for Highland Games Scoreboard
 * 
 * This file contains utility functions used throughout the application
 */

// Prevent direct access to this file
if (!defined('HIGHLAND_GAMES')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Read data from a JSON file
 * 
 * @param string $file Path to the JSON file
 * @return array|null The data from the JSON file or null on error
 */
function readJsonFile($file) {
    if (!file_exists($file)) {
        return null;
    }
    
    // Acquire a shared lock for reading
    $fp = fopen($file, 'r');
    if (!$fp) {
        return null;
    }
    
    flock($fp, LOCK_SH);
    $data = json_decode(file_get_contents($file), true);
    flock($fp, LOCK_UN);
    fclose($fp);
    
    return $data;
}

/**
 * Write data to a JSON file
 * 
 * @param string $file Path to the JSON file
 * @param array $data The data to write
 * @return bool True on success, false on failure
 */
function writeJsonFile($file, $data) {
    // Create directory if it doesn't exist
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Acquire an exclusive lock for writing
    $fp = fopen($file, 'w');
    if (!$fp) {
        return false;
    }
    
    $success = false;
    if (flock($fp, LOCK_EX)) {
        $success = (fwrite($fp, json_encode($data, JSON_PRETTY_PRINT)) !== false);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    
    return $success;
}

/**
 * Generate a unique ID
 * 
 * @param string $prefix Optional prefix for the ID
 * @return string A unique ID
 */
function generateId($prefix = '') {
    return $prefix . uniqid() . bin2hex(random_bytes(4));
}

/**
 * Sanitize input data
 * 
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Get all participants
 * 
 * @return array Array of participants
 */
function getParticipants() {
    $data = readJsonFile(PARTICIPANTS_FILE);
    return $data ? $data['participants'] : [];
}

/**
 * Get a participant by ID
 * 
 * @param string $id The participant ID
 * @return array|null The participant data or null if not found
 */
function getParticipant($id) {
    $participants = getParticipants();
    foreach ($participants as $participant) {
        if ($participant['id'] === $id) {
            return $participant;
        }
    }
    return null;
}

/**
 * Add a new participant
 * 
 * @param array $participant The participant data
 * @return bool True on success, false on failure
 */
function addParticipant($participant) {
    $data = readJsonFile(PARTICIPANTS_FILE);
    if (!$data) {
        $data = ['participants' => []];
    }
    
    // Generate ID if not provided
    if (!isset($participant['id'])) {
        $participant['id'] = generateId('p');
    }
    
    // Add created_at timestamp if not provided
    if (!isset($participant['created_at'])) {
        $participant['created_at'] = date('c');
    }
    
    // Initialize stats if not provided
    if (!isset($participant['stats'])) {
        $participant['stats'] = [
            'personal_bests' => [],
            'competition_history' => []
        ];
    }
    
    $data['participants'][] = $participant;
    return writeJsonFile(PARTICIPANTS_FILE, $data);
}

/**
 * Update a participant
 * 
 * @param string $id The participant ID
 * @param array $updatedData The updated participant data
 * @return bool True on success, false on failure
 */
function updateParticipant($id, $updatedData) {
    $data = readJsonFile(PARTICIPANTS_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['participants'] as $key => $participant) {
        if ($participant['id'] === $id) {
            // Preserve ID and created_at
            $updatedData['id'] = $id;
            if (isset($participant['created_at'])) {
                $updatedData['created_at'] = $participant['created_at'];
            }
            
            $data['participants'][$key] = $updatedData;
            return writeJsonFile(PARTICIPANTS_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Delete a participant
 * 
 * @param string $id The participant ID
 * @return bool True on success, false on failure
 */
function deleteParticipant($id) {
    $data = readJsonFile(PARTICIPANTS_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['participants'] as $key => $participant) {
        if ($participant['id'] === $id) {
            array_splice($data['participants'], $key, 1);
            return writeJsonFile(PARTICIPANTS_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Get all events
 * 
 * @return array Array of events
 */
function getEvents() {
    $data = readJsonFile(EVENTS_FILE);
    return $data ? $data['events'] : [];
}

/**
 * Get an event by ID
 * 
 * @param string $id The event ID
 * @return array|null The event data or null if not found
 */
function getEvent($id) {
    $events = getEvents();
    foreach ($events as $event) {
        if ($event['id'] === $id) {
            return $event;
        }
    }
    return null;
}

/**
 * Add a new event
 * 
 * @param array $event The event data
 * @return bool True on success, false on failure
 */
function addEvent($event) {
    $data = readJsonFile(EVENTS_FILE);
    if (!$data) {
        $data = ['events' => []];
    }
    
    // Generate ID if not provided
    if (!isset($event['id'])) {
        $event['id'] = generateId('e');
    }
    
    // Add created_at timestamp if not provided
    if (!isset($event['created_at'])) {
        $event['created_at'] = date('c');
    }
    
    $data['events'][] = $event;
    return writeJsonFile(EVENTS_FILE, $data);
}

/**
 * Update an event
 * 
 * @param string $id The event ID
 * @param array $updatedData The updated event data
 * @return bool True on success, false on failure
 */
function updateEvent($id, $updatedData) {
    $data = readJsonFile(EVENTS_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['events'] as $key => $event) {
        if ($event['id'] === $id) {
            // Preserve ID and created_at
            $updatedData['id'] = $id;
            if (isset($event['created_at'])) {
                $updatedData['created_at'] = $event['created_at'];
            }
            
            $data['events'][$key] = $updatedData;
            return writeJsonFile(EVENTS_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Delete an event
 * 
 * @param string $id The event ID
 * @return bool True on success, false on failure
 */
function deleteEvent($id) {
    $data = readJsonFile(EVENTS_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['events'] as $key => $event) {
        if ($event['id'] === $id) {
            array_splice($data['events'], $key, 1);
            return writeJsonFile(EVENTS_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Get all competitions
 * 
 * @return array Array of competitions
 */
function getCompetitions() {
    $data = readJsonFile(COMPETITIONS_FILE);
    return $data ? $data['competitions'] : [];
}

/**
 * Get a competition by ID
 * 
 * @param string $id The competition ID
 * @return array|null The competition data or null if not found
 */
function getCompetition($id) {
    $competitions = getCompetitions();
    foreach ($competitions as $competition) {
        if ($competition['id'] === $id) {
            return $competition;
        }
    }
    return null;
}

/**
 * Add a new competition
 * 
 * @param array $competition The competition data
 * @return bool True on success, false on failure
 */
function addCompetition($competition) {
    $data = readJsonFile(COMPETITIONS_FILE);
    if (!$data) {
        $data = ['competitions' => []];
    }
    
    // Generate ID if not provided
    if (!isset($competition['id'])) {
        $competition['id'] = generateId('comp');
    }
    
    // Add created_at timestamp if not provided
    if (!isset($competition['created_at'])) {
        $competition['created_at'] = date('c');
    }
    
    // Initialize arrays if not provided
    if (!isset($competition['event_ids'])) {
        $competition['event_ids'] = [];
    }
    if (!isset($competition['participant_ids'])) {
        $competition['participant_ids'] = [];
    }
    if (!isset($competition['category_ids'])) {
        $competition['category_ids'] = [];
    }
    
    // Set status to upcoming if not provided
    if (!isset($competition['status'])) {
        $competition['status'] = 'upcoming';
    }
    
    $data['competitions'][] = $competition;
    return writeJsonFile(COMPETITIONS_FILE, $data);
}

/**
 * Update a competition
 * 
 * @param string $id The competition ID
 * @param array $updatedData The updated competition data
 * @return bool True on success, false on failure
 */
function updateCompetition($id, $updatedData) {
    $data = readJsonFile(COMPETITIONS_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['competitions'] as $key => $competition) {
        if ($competition['id'] === $id) {
            // Preserve ID and created_at
            $updatedData['id'] = $id;
            if (isset($competition['created_at'])) {
                $updatedData['created_at'] = $competition['created_at'];
            }
            
            $data['competitions'][$key] = $updatedData;
            return writeJsonFile(COMPETITIONS_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Delete a competition
 * 
 * @param string $id The competition ID
 * @return bool True on success, false on failure
 */
function deleteCompetition($id) {
    $data = readJsonFile(COMPETITIONS_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['competitions'] as $key => $competition) {
        if ($competition['id'] === $id) {
            array_splice($data['competitions'], $key, 1);
            return writeJsonFile(COMPETITIONS_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Get all scores
 * 
 * @return array Array of scores
 */
function getScores() {
    $data = readJsonFile(SCORES_FILE);
    return $data ? $data['scores'] : [];
}

/**
 * Get scores for a specific competition
 * 
 * @param string $competitionId The competition ID
 * @return array Array of scores for the competition
 */
function getCompetitionScores($competitionId) {
    $scores = getScores();
    $competitionScores = [];
    
    foreach ($scores as $score) {
        if ($score['competition_id'] === $competitionId) {
            $competitionScores[] = $score;
        }
    }
    
    return $competitionScores;
}

/**
 * Get scores for a specific event in a competition
 * 
 * @param string $competitionId The competition ID
 * @param string $eventId The event ID
 * @return array Array of scores for the event in the competition
 */
function getEventScores($competitionId, $eventId) {
    $scores = getScores();
    $eventScores = [];
    
    foreach ($scores as $score) {
        if ($score['competition_id'] === $competitionId && $score['event_id'] === $eventId) {
            $eventScores[] = $score;
        }
    }
    
    return $eventScores;
}

/**
 * Get scores for a specific participant in a competition
 * 
 * @param string $competitionId The competition ID
 * @param string $participantId The participant ID
 * @return array Array of scores for the participant in the competition
 */
function getParticipantScores($competitionId, $participantId) {
    $scores = getScores();
    $participantScores = [];
    
    foreach ($scores as $score) {
        if ($score['competition_id'] === $competitionId && $score['participant_id'] === $participantId) {
            $participantScores[] = $score;
        }
    }
    
    return $participantScores;
}

/**
 * Add a new score
 * 
 * @param array $score The score data
 * @return bool True on success, false on failure
 */
function addScore($score) {
    $data = readJsonFile(SCORES_FILE);
    if (!$data) {
        $data = ['scores' => []];
    }
    
    // Generate ID if not provided
    if (!isset($score['id'])) {
        $score['id'] = generateId('s');
    }
    
    // Add recorded_at timestamp if not provided
    if (!isset($score['recorded_at'])) {
        $score['recorded_at'] = date('c');
    }
    
    // Check if a score already exists for this participant in this event
    foreach ($data['scores'] as $key => $existingScore) {
        if ($existingScore['competition_id'] === $score['competition_id'] &&
            $existingScore['event_id'] === $score['event_id'] &&
            $existingScore['participant_id'] === $score['participant_id']) {
            // Update existing score
            $data['scores'][$key] = $score;
            return writeJsonFile(SCORES_FILE, $data);
        }
    }
    
    // Add new score
    $data['scores'][] = $score;
    return writeJsonFile(SCORES_FILE, $data);
}

/**
 * Update a score
 * 
 * @param string $id The score ID
 * @param array $updatedData The updated score data
 * @return bool True on success, false on failure
 */
function updateScore($id, $updatedData) {
    $data = readJsonFile(SCORES_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['scores'] as $key => $score) {
        if ($score['id'] === $id) {
            // Preserve ID
            $updatedData['id'] = $id;
            
            // Update recorded_at timestamp
            $updatedData['recorded_at'] = date('c');
            
            $data['scores'][$key] = $updatedData;
            return writeJsonFile(SCORES_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Delete a score
 * 
 * @param string $id The score ID
 * @return bool True on success, false on failure
 */
function deleteScore($id) {
    $data = readJsonFile(SCORES_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['scores'] as $key => $score) {
        if ($score['id'] === $id) {
            array_splice($data['scores'], $key, 1);
            return writeJsonFile(SCORES_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Get all categories
 * 
 * @return array Array of categories
 */
function getCategories() {
    $data = readJsonFile(CATEGORIES_FILE);
    return $data ? $data['categories'] : [];
}

/**
 * Get a category by ID
 * 
 * @param string $id The category ID
 * @return array|null The category data or null if not found
 */
function getCategory($id) {
    $categories = getCategories();
    foreach ($categories as $category) {
        if ($category['id'] === $id) {
            return $category;
        }
    }
    return null;
}

/**
 * Add a new category
 * 
 * @param array $category The category data
 * @return bool True on success, false on failure
 */
function addCategory($category) {
    $data = readJsonFile(CATEGORIES_FILE);
    if (!$data) {
        $data = ['categories' => []];
    }
    
    // Generate ID if not provided
    if (!isset($category['id'])) {
        $category['id'] = generateId('c');
    }
    
    $data['categories'][] = $category;
    return writeJsonFile(CATEGORIES_FILE, $data);
}

/**
 * Update a category
 * 
 * @param string $id The category ID
 * @param array $updatedData The updated category data
 * @return bool True on success, false on failure
 */
function updateCategory($id, $updatedData) {
    $data = readJsonFile(CATEGORIES_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['categories'] as $key => $category) {
        if ($category['id'] === $id) {
            // Preserve ID
            $updatedData['id'] = $id;
            
            $data['categories'][$key] = $updatedData;
            return writeJsonFile(CATEGORIES_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Delete a category
 * 
 * @param string $id The category ID
 * @return bool True on success, false on failure
 */
function deleteCategory($id) {
    $data = readJsonFile(CATEGORIES_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['categories'] as $key => $category) {
        if ($category['id'] === $id) {
            array_splice($data['categories'], $key, 1);
            return writeJsonFile(CATEGORIES_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Get all teams
 * 
 * @return array Array of teams
 */
function getTeams() {
    $data = readJsonFile(TEAMS_FILE);
    return $data ? $data['teams'] : [];
}

/**
 * Get a team by ID
 * 
 * @param string $id The team ID
 * @return array|null The team data or null if not found
 */
function getTeam($id) {
    $teams = getTeams();
    foreach ($teams as $team) {
        if ($team['id'] === $id) {
            return $team;
        }
    }
    return null;
}

/**
 * Add a new team
 * 
 * @param array $team The team data
 * @return bool True on success, false on failure
 */
function addTeam($team) {
    $data = readJsonFile(TEAMS_FILE);
    if (!$data) {
        $data = ['teams' => []];
    }
    
    // Generate ID if not provided
    if (!isset($team['id'])) {
        $team['id'] = generateId('t');
    }
    
    $data['teams'][] = $team;
    return writeJsonFile(TEAMS_FILE, $data);
}

/**
 * Update a team
 * 
 * @param string $id The team ID
 * @param array $updatedData The updated team data
 * @return bool True on success, false on failure
 */
function updateTeam($id, $updatedData) {
    $data = readJsonFile(TEAMS_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['teams'] as $key => $team) {
        if ($team['id'] === $id) {
            // Preserve ID
            $updatedData['id'] = $id;
            
            $data['teams'][$key] = $updatedData;
            return writeJsonFile(TEAMS_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Delete a team
 * 
 * @param string $id The team ID
 * @return bool True on success, false on failure
 */
function deleteTeam($id) {
    $data = readJsonFile(TEAMS_FILE);
    if (!$data) {
        return false;
    }
    
    foreach ($data['teams'] as $key => $team) {
        if ($team['id'] === $id) {
            array_splice($data['teams'], $key, 1);
            return writeJsonFile(TEAMS_FILE, $data);
        }
    }
    
    return false;
}

/**
 * Calculate rankings for a competition
 * 
 * @param string $competitionId The competition ID
 * @return array Array of participant rankings
 */
function calculateRankings($competitionId) {
    $competition = getCompetition($competitionId);
    if (!$competition) {
        return [];
    }
    
    $participants = [];
    foreach ($competition['participant_ids'] as $participantId) {
        $participant = getParticipant($participantId);
        if ($participant) {
            $participants[$participantId] = [
                'id' => $participantId,
                'name' => $participant['name'],
                'team_id' => $participant['team_id'] ?? null,
                'category_ids' => $participant['category_ids'] ?? [],
                'total_points' => 0,
                'event_points' => []
            ];
        }
    }
    
    $scores = getCompetitionScores($competitionId);
    foreach ($scores as $score) {
        $participantId = $score['participant_id'];
        $eventId = $score['event_id'];
        
        if (isset($participants[$participantId])) {
            $participants[$participantId]['total_points'] += $score['points'];
            $participants[$participantId]['event_points'][$eventId] = $score['points'];
        }
    }
    
    // Convert to array and sort by total points (descending)
    $rankings = array_values($participants);
    usort($rankings, function($a, $b) {
        return $b['total_points'] - $a['total_points'];
    });
    
    // Add rank
    $rank = 1;
    $prevPoints = null;
    $sameRankCount = 0;
    
    foreach ($rankings as $key => $participant) {
        if ($prevPoints !== null && $participant['total_points'] < $prevPoints) {
            $rank += $sameRankCount;
            $sameRankCount = 1;
        } else if ($prevPoints !== null && $participant['total_points'] === $prevPoints) {
            $sameRankCount++;
        } else {
            $sameRankCount = 1;
        }
        
        $rankings[$key]['rank'] = $rank;
        $prevPoints = $participant['total_points'];
    }
    
    return $rankings;
}

/**
 * Get participants by category
 * 
 * @param string $categoryId The category ID
 * @return array Array of participants in the category
 */
function getParticipantsByCategory($categoryId) {
    $participants = getParticipants();
    $categoryParticipants = [];
    
    foreach ($participants as $participant) {
        if (isset($participant['category_ids']) && in_array($categoryId, $participant['category_ids'])) {
            $categoryParticipants[] = $participant;
        }
    }
    
    return $categoryParticipants;
}

/**
 * Get participants by team
 * 
 * @param string $teamId The team ID
 * @return array Array of participants in the team
 */
function getParticipantsByTeam($teamId) {
    $participants = getParticipants();
    $teamParticipants = [];
    
    foreach ($participants as $participant) {
        if (isset($participant['team_id']) && $participant['team_id'] === $teamId) {
            $teamParticipants[] = $participant;
        }
    }
    
    return $teamParticipants;
}

/**
 * Update participant stats
 * 
 * @param string $participantId The participant ID
 * @param string $competitionId The competition ID
 * @return bool True on success, false on failure
 */
function updateParticipantStats($participantId, $competitionId) {
    $participant = getParticipant($participantId);
    if (!$participant) {
        return false;
    }
    
    $competition = getCompetition($competitionId);
    if (!$competition) {
        return false;
    }
    
    $scores = getParticipantScores($competitionId, $participantId);
    if (empty($scores)) {
        return true; // No scores to update
    }
    
    // Update personal bests
    $personalBests = $participant['stats']['personal_bests'] ?? [];
    
    foreach ($scores as $score) {
        $eventId = $score['event_id'];
        $points = $score['points'];
        
        if (!isset($personalBests[$eventId]) || $points > $personalBests[$eventId]) {
            $personalBests[$eventId] = $points;
        }
    }
    
    // Update competition history
    $competitionHistory = $participant['stats']['competition_history'] ?? [];
    
    // Check if this competition is already in history
    $competitionExists = false;
    foreach ($competitionHistory as $key => $history) {
        if ($history['competition_id'] === $competitionId) {
            $competitionHistory[$key] = [
                'competition_id' => $competitionId,
                'competition_name' => $competition['name'],
                'date' => $competition['date'],
                'scores' => $scores,
                'total_points' => array_sum(array_column($scores, 'points'))
            ];
            $competitionExists = true;
            break;
        }
    }
    
    // Add to history if not exists
    if (!$competitionExists) {
        $competitionHistory[] = [
            'competition_id' => $competitionId,
            'competition_name' => $competition['name'],
            'date' => $competition['date'],
            'scores' => $scores,
            'total_points' => array_sum(array_column($scores, 'points'))
        ];
    }
    
    // Sort competition history by date (newest first)
    usort($competitionHistory, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // Update participant stats
    $participant['stats'] = [
        'personal_bests' => $personalBests,
        'competition_history' => $competitionHistory
    ];
    
    return updateParticipant($participantId, $participant);
}

/**
 * Format date for display
 * 
 * @param string $date Date string in any format
 * @param string $format Output format (default: 'F j, Y')
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Check if a competition is complete
 * 
 * @param string $competitionId The competition ID
 * @return bool True if complete, false otherwise
 */
function isCompetitionComplete($competitionId) {
    $competition = getCompetition($competitionId);
    if (!$competition) {
        return false;
    }
    
    // If status is already set to completed
    if ($competition['status'] === 'completed') {
        return true;
    }
    
    // Check if all events have scores for all participants
    $eventIds = $competition['event_ids'];
    $participantIds = $competition['participant_ids'];
    $scores = getCompetitionScores($competitionId);
    
    $scoreCount = 0;
    $requiredScores = count($eventIds) * count($participantIds);
    
    foreach ($scores as $score) {
        if (in_array($score['event_id'], $eventIds) && in_array($score['participant_id'], $participantIds)) {
            $scoreCount++;
        }
    }
    
    return $scoreCount >= $requiredScores;
}

/**
 * Get active competitions
 * 
 * @return array Array of active competitions
 */
function getActiveCompetitions() {
    $competitions = getCompetitions();
    $activeCompetitions = [];
    
    foreach ($competitions as $competition) {
        if ($competition['status'] === 'active') {
            $activeCompetitions[] = $competition;
        }
    }
    
    return $activeCompetitions;
}

/**
 * Get upcoming competitions
 * 
 * @return array Array of upcoming competitions
 */
function getUpcomingCompetitions() {
    $competitions = getCompetitions();
    $upcomingCompetitions = [];
    
    foreach ($competitions as $competition) {
        if ($competition['status'] === 'upcoming') {
            $upcomingCompetitions[] = $competition;
        }
    }
    
    // Sort by date (soonest first)
    usort($upcomingCompetitions, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    
    return $upcomingCompetitions;
}

/**
 * Get completed competitions
 * 
 * @return array Array of completed competitions
 */
function getCompletedCompetitions() {
    $competitions = getCompetitions();
    $completedCompetitions = [];
    
    foreach ($competitions as $competition) {
        if ($competition['status'] === 'completed') {
            $completedCompetitions[] = $competition;
        }
    }
    
    // Sort by date (most recent first)
    usort($completedCompetitions, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return $completedCompetitions;
}

/**
 * Generate a CSRF token
 * 
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in as admin
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Redirect to a URL
 * 
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Display a flash message
 * 
 * @param string $type The message type (success, error, warning, info)
 * @param string $message The message text
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null The flash message or null if none
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}