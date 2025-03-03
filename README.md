# Highland Games Scoreboard

A PHP-based scoreboard system for Highland Games competitions with real-time updates and a comprehensive admin interface.

## Features

- **Flexible Event Management**: Create and manage any type of event with customizable scoring.
- **Participant Management**: Add participants with team affiliations and category assignments.
- **Real-time Updates**: Scores and rankings update in real-time without page refreshes.
- **Responsive Design**: Works great on both desktop and mobile devices.
- **Comprehensive Admin Interface**: Easy-to-use interface for managing competitions, events, participants, and scores.
- **JSON Database**: Uses JSON files for data storage, making it compatible with standard shared web hosting.
- **Team Support**: Organize participants into teams.
- **Category Support**: Group participants by categories (e.g., weight classes, age groups, gender).
- **Participant Statistics**: Track personal bests and competition history for each participant.

## Requirements

- PHP 8.2 or higher
- Apache web server with mod_rewrite enabled
- Standard shared web hosting (no special requirements)

## Installation

1. Upload all files to your web server.
2. Ensure the `data` directory is writable by the web server:
   ```
   chmod 755 data
   ```
3. Access the website through your browser.
4. **Important**: Change the default admin password by editing the `includes/config.php` file.

## Directory Structure

```
highland-games/
├── admin/                     # Admin interface files
├── api/                       # AJAX endpoints
├── assets/                    # Static assets (CSS, JS, images)
├── data/                      # JSON data storage
├── includes/                  # Shared PHP files
└── templates/                 # Page templates
```

## Admin Interface

The admin interface allows you to:

1. **Manage Competitions**: Create, edit, and delete competitions.
2. **Manage Events**: Define events with custom scoring systems.
3. **Manage Participants**: Add participants and assign them to teams and categories.
4. **Record Scores**: Enter scores for participants in events.
5. **Manage Categories**: Create categories for grouping participants.
6. **Manage Teams**: Create teams and assign participants to them.

## Public Interface

The public interface displays:

1. **Active Competitions**: Currently running competitions.
2. **Upcoming Competitions**: Scheduled future competitions.
3. **Past Competitions**: Completed competitions with results.
4. **Competition Details**: Events, participants, and scores for a specific competition.
5. **Event Scores**: Scores for a specific event in a competition.
6. **Participant Profiles**: Details about a participant, including personal bests and competition history.

## Workflow

1. Create categories and teams (optional).
2. Add participants and assign them to categories and teams.
3. Create events with appropriate scoring systems.
4. Create a competition and assign events and participants to it.
5. Activate the competition when it's time to start.
6. Record scores as events are completed.
7. Mark the competition as completed when all events are finished.

## Security

- The admin interface is protected by a username and password.
- JSON data files are protected from direct access.
- CSRF protection is implemented for all forms.
- Input validation and sanitization is performed for all user inputs.

## Customization

- Edit `includes/config.php` to change site name, description, and admin credentials.
- Modify `assets/css/style.css` to customize the appearance.
- Edit `includes/header.php` and `includes/footer.php` to change the layout.

## License

This project is released under the Unlicense. See the LICENSE file for details.