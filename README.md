# Popcorn Protocol 🍿

A highly purpose-built application designed to manage the cinema-going habits of exactly two people (Alex & Casper). It tracks who paid for tickets, who is due to pay for snacks, and provides deep insights into movie habits.

## 🤝 The Protocol (How it Works)

This application is not a generic tracker. It enforces a strict fairness protocol:

1.  **Ticket Payer**: Determined by the Google Calendar event details (parsed by AI).
2.  **Snack Payer**: determined by the "Turn" system.
    -   The front page displays **"Current Payer: [Name]"**.
    -   When IT IS your turn, you pay for the snacks/drinks at the cinema.
    -   **The Flip**: After paying, you click the component on the dashboard.
    -   **Logic**: The app assigns the cost of the *current/closest* movie's snacks to YOU, and then flips the "Current Payer" status to the *other* person.
    -   Result: The system knows you paid for this one, and the other person is now on the hook for next time.

## 🌟 Features

-   **Two-Player Mode**: Hardcoded logic for two specific users to ensure perfect balance.
-   **Passkey Authentication**: No passwords. Login uses biometric/device passkeys for instant, secure access.
-   **Google Calendar Sync**: Automatically imports events, using AI to extract metadata (Cinema, Seat, Price).
-   **Smart Backfill**: Automatically fetches runtime and metadata from TMDB for every movie.
-   **LiveStats Dashboard**:
    -   **Fairness Meter**: Who has spent more?
    -   **Cost per Hour**: Are we getting value for money?
    -   **Weekly Habits**: When do we go to the movies?

## 🚀 Setup

### Prerequisites

-   PHP 8.2+
-   Composer & Node.js
-   **Hardware**: A device with Biometric support (TouchID/FaceID) for Passkeys.
-   Google Cloud Service Account (Calendar API)
-   Gemini API Key
-   TMDB API Key

### Installation

1.  **Clone & Install**:
    ```bash
    git clone <repo>
    cd cinema
    composer install
    npm install && npm run build
    ```

2.  **Environment**:
    Configure `.env` with your API keys.

    ```ini
    GOOGLE_CALENDAR_ID=...
    GOOGLE_CALENDAR_CREDENTIALS_B64=...
    GEMINI_API_KEY=...
    TMDB_API_KEY=...
    ```

3.  **Database**:
    ```bash
    php artisan migrate --seed
    ```
    *Note: The seeder creates the initial two users needed for the protocol.*

## 📅 Usage

### synchronizing Events
```bash
php artisan calendar:import
```
*Run this cron job hourly to keep stats up to date.*

### The "Flip" (Front Page)
1.  Login via Passkey.
2.  If your name is on the screen, **YOU PAY** for the snacks.
3.  Click your name/card to **FLIP** the turn to the other person.
4.  The system records your payment for the nearest movie.

## 🧪 Testing

```bash
php artisan test
```

## 🛠️ Troubleshooting

-   **Import finds 0 events**: Ensure the Service Account email is a guest on the event.
-   **"Flip" didn't update a movie**: The flip logic looks for the *closest* movie (past or upcoming). If no movie is near, it just swaps turns without assigning cost.

