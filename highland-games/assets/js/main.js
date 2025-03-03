/**
 * Highland Games Scoreboard - Main JavaScript
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // AJAX polling for real-time updates
    initializeAjaxPolling();
    
    // Initialize any forms with AJAX submission
    initializeAjaxForms();
    
    // Initialize competition status updates
    initializeCompetitionStatus();
    
    // Initialize score entry form
    initializeScoreEntry();
});

/**
 * Initialize AJAX polling for real-time updates
 */
function initializeAjaxPolling() {
    // Check if we're on a page that needs real-time updates
    const scoreboardContainer = document.getElementById('scoreboard-container');
    const rankingsContainer = document.getElementById('rankings-container');
    
    if (scoreboardContainer || rankingsContainer) {
        // Get the competition ID from the data attribute
        const competitionId = scoreboardContainer ? 
            scoreboardContainer.getAttribute('data-competition-id') : 
            rankingsContainer.getAttribute('data-competition-id');
        
        if (competitionId) {
            // Start polling for updates
            pollForUpdates(competitionId);
        }
    }
}

/**
 * Poll for updates to scores and rankings
 * 
 * @param {string} competitionId The competition ID
 */
function pollForUpdates(competitionId) {
    // Store the current data for comparison
    let currentScores = {};
    let currentRankings = {};
    
    // Initial load
    updateScores(competitionId, currentScores);
    updateRankings(competitionId, currentRankings);
    
    // Set up polling interval (default: 5 seconds)
    const pollInterval = window.AJAX_POLL_INTERVAL || 5000;
    
    setInterval(function() {
        updateScores(competitionId, currentScores);
        updateRankings(competitionId, currentRankings);
    }, pollInterval);
}

/**
 * Update scores with AJAX
 * 
 * @param {string} competitionId The competition ID
 * @param {object} currentScores Reference to current scores for comparison
 */
function updateScores(competitionId, currentScores) {
    const scoreboardContainer = document.getElementById('scoreboard-container');
    if (!scoreboardContainer) return;
    
    // Get the event ID if we're on an event page
    const eventId = scoreboardContainer.getAttribute('data-event-id') || null;
    
    // Show loading indicator
    const loadingIndicator = scoreboardContainer.querySelector('.loading-indicator');
    if (loadingIndicator) loadingIndicator.style.display = 'inline-block';
    
    // Make AJAX request
    $.ajax({
        url: 'api/get_scores.php',
        type: 'GET',
        data: {
            competition_id: competitionId,
            event_id: eventId
        },
        dataType: 'json',
        success: function(data) {
            // Hide loading indicator
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            
            // Update scores in the DOM
            updateScoresInDOM(data, currentScores);
            
            // Update the reference to current scores
            Object.assign(currentScores, data);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching scores:', error);
            // Hide loading indicator
            if (loadingIndicator) loadingIndicator.style.display = 'none';
        }
    });
}

/**
 * Update scores in the DOM
 * 
 * @param {object} newScores The new scores data
 * @param {object} currentScores The current scores for comparison
 */
function updateScoresInDOM(newScores, currentScores) {
    // Loop through each score
    for (const eventId in newScores) {
        for (const participantId in newScores[eventId]) {
            const score = newScores[eventId][participantId];
            const scoreCell = document.querySelector(`#score-${eventId}-${participantId}`);
            
            if (scoreCell) {
                // Check if the score has changed
                const hasChanged = !currentScores[eventId] || 
                                  !currentScores[eventId][participantId] || 
                                  currentScores[eventId][participantId].points !== score.points;
                
                // Update the score
                scoreCell.textContent = score.points;
                
                // Highlight the cell if the score has changed
                if (hasChanged) {
                    scoreCell.classList.add('score-highlight');
                    setTimeout(function() {
                        scoreCell.classList.remove('score-highlight');
                    }, 2000);
                }
            }
        }
    }
}

/**
 * Update rankings with AJAX
 * 
 * @param {string} competitionId The competition ID
 * @param {object} currentRankings Reference to current rankings for comparison
 */
function updateRankings(competitionId, currentRankings) {
    const rankingsContainer = document.getElementById('rankings-container');
    if (!rankingsContainer) return;
    
    // Get the category ID if we're filtering by category
    const categoryId = rankingsContainer.getAttribute('data-category-id') || null;
    
    // Show loading indicator
    const loadingIndicator = rankingsContainer.querySelector('.loading-indicator');
    if (loadingIndicator) loadingIndicator.style.display = 'inline-block';
    
    // Make AJAX request
    $.ajax({
        url: 'api/get_rankings.php',
        type: 'GET',
        data: {
            competition_id: competitionId,
            category_id: categoryId
        },
        dataType: 'json',
        success: function(data) {
            // Hide loading indicator
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            
            // Update rankings in the DOM
            updateRankingsInDOM(data, currentRankings);
            
            // Update the reference to current rankings
            Object.assign(currentRankings, data);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching rankings:', error);
            // Hide loading indicator
            if (loadingIndicator) loadingIndicator.style.display = 'none';
        }
    });
}

/**
 * Update rankings in the DOM
 * 
 * @param {object} newRankings The new rankings data
 * @param {object} currentRankings The current rankings for comparison
 */
function updateRankingsInDOM(newRankings, currentRankings) {
    const rankingsTable = document.querySelector('#rankings-table tbody');
    if (!rankingsTable) return;
    
    // Clear existing rows if the structure has changed
    if (newRankings.length !== Object.keys(currentRankings).length) {
        rankingsTable.innerHTML = '';
    }
    
    // Loop through each participant
    newRankings.forEach(function(participant, index) {
        let row = document.querySelector(`#ranking-${participant.id}`);
        
        // Create row if it doesn't exist
        if (!row) {
            row = document.createElement('tr');
            row.id = `ranking-${participant.id}`;
            rankingsTable.appendChild(row);
        }
        
        // Check if the ranking has changed
        const hasChanged = !currentRankings[index] || 
                          currentRankings[index].rank !== participant.rank || 
                          currentRankings[index].total_points !== participant.total_points;
        
        // Update the row content
        row.innerHTML = `
            <td class="text-center">${participant.rank}</td>
            <td>${participant.name}</td>
            <td>${participant.team_name || '-'}</td>
            <td class="text-center">${participant.total_points}</td>
        `;
        
        // Add rank class
        row.className = '';
        if (participant.rank === 1) row.classList.add('rank-1');
        if (participant.rank === 2) row.classList.add('rank-2');
        if (participant.rank === 3) row.classList.add('rank-3');
        
        // Highlight the row if the ranking has changed
        if (hasChanged) {
            row.classList.add('score-highlight');
            setTimeout(function() {
                row.classList.remove('score-highlight');
            }, 2000);
        }
    });
}

/**
 * Initialize forms with AJAX submission
 */
function initializeAjaxForms() {
    const ajaxForms = document.querySelectorAll('.ajax-form');
    
    ajaxForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading indicator
            const submitButton = form.querySelector('[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="loading-spinner"></span> Submitting...';
            submitButton.disabled = true;
            
            // Get form data
            const formData = new FormData(form);
            
            // Make AJAX request
            $.ajax({
                url: form.action,
                type: form.method,
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    // Reset button
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                    
                    // Handle response
                    if (response.success) {
                        // Show success message
                        showAlert('success', response.message);
                        
                        // Reset form if specified
                        if (form.getAttribute('data-reset-on-success') === 'true') {
                            form.reset();
                        }
                        
                        // Redirect if specified
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                        
                        // Reload if specified
                        if (form.getAttribute('data-reload-on-success') === 'true') {
                            window.location.reload();
                        }
                    } else {
                        // Show error message
                        showAlert('danger', response.message || 'An error occurred.');
                    }
                },
                error: function(xhr, status, error) {
                    // Reset button
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                    
                    // Show error message
                    showAlert('danger', 'An error occurred while submitting the form.');
                    console.error('Form submission error:', error);
                }
            });
        });
    });
}

/**
 * Show an alert message
 * 
 * @param {string} type The alert type (success, danger, warning, info)
 * @param {string} message The message to display
 */
function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        alert.classList.remove('show');
        setTimeout(function() {
            alertContainer.removeChild(alert);
        }, 150);
    }, 5000);
}

/**
 * Initialize competition status updates
 */
function initializeCompetitionStatus() {
    const statusButtons = document.querySelectorAll('.competition-status-btn');
    
    statusButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const competitionId = this.getAttribute('data-competition-id');
            const newStatus = this.getAttribute('data-status');
            
            if (!competitionId || !newStatus) return;
            
            // Confirm status change
            if (!confirm(`Are you sure you want to change the status to "${newStatus}"?`)) {
                return;
            }
            
            // Show loading indicator
            const originalButtonText = this.innerHTML;
            this.innerHTML = '<span class="loading-spinner"></span> Updating...';
            this.disabled = true;
            
            // Make AJAX request
            $.ajax({
                url: 'update_competition_status.php',
                type: 'POST',
                data: {
                    competition_id: competitionId,
                    status: newStatus,
                    csrf_token: document.querySelector('input[name="csrf_token"]').value
                },
                dataType: 'json',
                success: (response) => {
                    // Reset button
                    this.innerHTML = originalButtonText;
                    this.disabled = false;
                    
                    // Handle response
                    if (response.success) {
                        // Show success message
                        showAlert('success', response.message);
                        
                        // Reload page
                        window.location.reload();
                    } else {
                        // Show error message
                        showAlert('danger', response.message || 'An error occurred.');
                    }
                },
                error: (xhr, status, error) => {
                    // Reset button
                    this.innerHTML = originalButtonText;
                    this.disabled = false;
                    
                    // Show error message
                    showAlert('danger', 'An error occurred while updating the status.');
                    console.error('Status update error:', error);
                }
            });
        });
    });
}

/**
 * Initialize score entry form
 */
function initializeScoreEntry() {
    const scoreForm = document.getElementById('score-entry-form');
    if (!scoreForm) return;
    
    // Handle competition selection
    const competitionSelect = document.getElementById('competition_id');
    if (competitionSelect) {
        competitionSelect.addEventListener('change', function() {
            loadCompetitionData(this.value);
        });
        
        // Load initial data if a competition is selected
        if (competitionSelect.value) {
            loadCompetitionData(competitionSelect.value);
        }
    }
}

/**
 * Load competition data (events and participants)
 * 
 * @param {string} competitionId The competition ID
 */
function loadCompetitionData(competitionId) {
    if (!competitionId) return;
    
    const eventSelect = document.getElementById('event_id');
    const participantSelect = document.getElementById('participant_id');
    
    if (!eventSelect || !participantSelect) return;
    
    // Clear existing options
    eventSelect.innerHTML = '<option value="">Select Event</option>';
    participantSelect.innerHTML = '<option value="">Select Participant</option>';
    
    // Disable selects while loading
    eventSelect.disabled = true;
    participantSelect.disabled = true;
    
    // Show loading indicators
    const eventLoading = document.getElementById('event-loading');
    const participantLoading = document.getElementById('participant-loading');
    
    if (eventLoading) eventLoading.style.display = 'inline-block';
    if (participantLoading) participantLoading.style.display = 'inline-block';
    
    // Load events
    $.ajax({
        url: 'get_competition_events.php',
        type: 'GET',
        data: { competition_id: competitionId },
        dataType: 'json',
        success: function(events) {
            // Hide loading indicator
            if (eventLoading) eventLoading.style.display = 'none';
            
            // Enable select
            eventSelect.disabled = false;
            
            // Add options
            events.forEach(function(event) {
                const option = document.createElement('option');
                option.value = event.id;
                option.textContent = event.name;
                eventSelect.appendChild(option);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error loading events:', error);
            // Hide loading indicator
            if (eventLoading) eventLoading.style.display = 'none';
            // Enable select
            eventSelect.disabled = false;
        }
    });
    
    // Load participants
    $.ajax({
        url: 'get_competition_participants.php',
        type: 'GET',
        data: { competition_id: competitionId },
        dataType: 'json',
        success: function(participants) {
            // Hide loading indicator
            if (participantLoading) participantLoading.style.display = 'none';
            
            // Enable select
            participantSelect.disabled = false;
            
            // Add options
            participants.forEach(function(participant) {
                const option = document.createElement('option');
                option.value = participant.id;
                option.textContent = participant.name;
                participantSelect.appendChild(option);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error loading participants:', error);
            // Hide loading indicator
            if (participantLoading) participantLoading.style.display = 'none';
            // Enable select
            participantSelect.disabled = false;
        }
    });
}