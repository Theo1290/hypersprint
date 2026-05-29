# Hypersprint 🔥

> Competitive real-time speed typing platform built with Vue 3, Vite, Pinia, Bootstrap 5, PHP REST APIs, MySQL persistence, and reactive multiplayer gameplay systems.

Hypersprint is a reactive multiplayer typing platform focused on competitive gameplay, real-time synchronization, social interaction systems, and terminal-inspired user experience design.

The application combines:

* Reactive frontend rendering
* Multiplayer race synchronization
* Persistent progression systems
* Social interaction features
* Live statistic tracking
* REST API communication
* Responsive gameplay architecture

---

# 📚 Table of Contents

* [Project Overview](#-project-overview)
* [Design Intent](#-design-intent)
* [Core Application Features](#-core-application-features)
* [Gameplay Architecture](#-gameplay-architecture)
* [Multiplayer Matchmaking System](#-multiplayer-matchmaking-system)
* [Authentication & Security](#-authentication--security)
* [Social Systems](#-social-systems)
* [CRUD Functionality](#-crud-functionality)
* [Search, Filtering & Sorting](#-search-filtering--sorting)
* [Responsive Design](#-responsive-design)
* [Accessibility & Usability Evaluation](#-accessibility--usability-evaluation)
* [Innovative Features](#-innovative-features)
* [Advanced Technical Feature](#-advanced-technical-feature)
* [Frontend Architecture](#-frontend-architecture)
* [Database & Persistence Layer](#-database--persistence-layer)
* [REST API Architecture](#-rest-api-architecture)
* [Website Pages](#-website-pages)
* [Reusable Components](#-reusable-components)
* [Functional Requirement Mapping](#-functional-requirement-mapping)
* [Technical Requirement Mapping](#-technical-requirement-mapping)
* [Coding Standards & Tooling](#-coding-standards--tooling)
* [Deployment](#-deployment)
* [Screenshots Showcase](#-screenshots-showcase)
* [Testing & Validation](#-testing--validation)
* [Getting Started](#-getting-started)
* [Scripts](#-scripts)
* [Conclusion](#-conclusion)

---

# 🎯 Project Overview

Hypersprint was developed as a modern competitive web application designed to transform traditional typing practice into an engaging multiplayer gameplay experience.

Rather than functioning as a simple typing utility, the platform focuses heavily on:

* Real-time responsiveness
* Reactive gameplay systems
* Competitive progression
* Multiplayer synchronization
* Social interaction
* Immersive interface design

The project combines modern frontend architecture with gameplay-focused interaction systems to create an experience that feels closer to an online competitive game than a conventional productivity application.

The application targets users interested in:

* Typing improvement
* Competitive skill progression
* Multiplayer gameplay
* Achievement systems
* Social comparison mechanics

---

# 🎨 Design Intent

The visual and interaction design of Hypersprint intentionally adopts a retro-futuristic Y2K terminal-inspired aesthetic.

The interface was designed around several primary UX goals:

## Core UX Goals

* Maintain uninterrupted typing flow
* Reduce visual distraction
* Provide instant correctness feedback
* Support competitive focus states
* Deliver highly responsive gameplay interaction
* Create a distinctive gameplay identity

## Visual Design Language

The visual identity incorporates:

* Cyberpunk-inspired typography
* Neon glow effects
* Radar-grid interface patterns
* Terminal-like layouts
* Battlegrid-inspired gameplay rendering

## Gameplay Philosophy

The gameplay experience prioritizes:

* Low-latency interaction
* Rapid visual feedback
* Reactive rendering
* Automatic focus handling
* Real-time stat calculation.

The goal was to make typing itself feel competitive and rewarding rather than purely educational.

---

# 🚀 Core Application Features

## ⚡ Reactive Typing Engine

The typing engine validates user input character-by-character using Vue reactive state systems.

Core functionality includes:

* Live correctness validation
* Dynamic cursor rendering
* Word progression tracking
* Overflow character detection
* Instant gameplay updates

The gameplay renderer updates in real time using computed properties and reactive rendering logic.

---

## 🏁 Multiplayer Matchmaking

The application includes a real-time multiplayer matchmaking system using:

* Race lobbies
* Synchronized race states
* Event-driven updates
* Real-time communication

Players can:

* Enter matchmaking queues
* Receive synchronized race invitations
* Compete in live races
* Compare performance metrics

---

## 📊 Persistent Statistics Tracking

Gameplay metrics are permanently persisted and tracked through the backend database.

Tracked statistics include:

* Highest WPM
* Average WPM
* Average accuracy
* Challenge completion count
* Multiplayer history
* Progression levels

---

## 🎖 Achievement Progression

Achievement systems reward players for:

* Reaching performance milestones
* Completing gameplay challenges
* Improving typing speed
* Sustained platform engagement

Persistent progression systems encourage long-term replayability.

---

## 👥 Social Features

The application includes social systems enabling:

* Player search
* Friend requests
* Friend acceptance/decline flows
* Friend removal
* Profile comparison
* Social activity systems

The social architecture transforms the application from a single-user tool into a competitive social platform.

---

## 📱 Responsive Interface System

The frontend was designed responsively using:

* Bootstrap 5 grid systems
* Responsive utility classes
* Modular component rendering
* Adaptive layouts

The application is intended to support:

* Desktop devices
* Tablets
* Mobile devices

---

# 🎮 Gameplay Architecture

## Core Gameplay Views

Primary gameplay systems are implemented within:

* `SingleplayerView.vue`
* `MultiplayerView.vue`
* `RaceView.vue`

These components manage:

* Typing state
* Gameplay lifecycle
* Race synchronization
* Stat calculation
* Challenge rendering
* Player progression

---

## Hidden Input Rendering Architecture

Gameplay uses a hidden textarea input model:

```vue
<textarea
  ref="hiddenInputRef"
  v-model="userInput"
  @input="handleInput"
  class="stealth-input"
></textarea>
```

This architecture allows:

* Custom cursor rendering
* Independent gameplay styling
* Smooth visual transitions
* Terminal-style rendering
* Uninterrupted gameplay focus

The visual gameplay layer is rendered independently from the actual input element.

This was implemented to overcome limitations of standard HTML text rendering.

---

## Character Validation System

Each typed character is validated independently.

Gameplay systems dynamically calculate:

* Current word index
* Current character index
* Completed words
* Incorrect characters
* Extra overflow characters
* Challenge progression

Correct and incorrect characters are rendered with separate visual states.

---

## Reactive State Management

Vue computed properties and reactive state systems drive gameplay updates.

Example:

```javascript
const currentWordIndex = computed(() => typedWords.value.length - 1)
```

Reactive rendering ensures immediate gameplay feedback without requiring manual DOM updates.

---

## Live WPM Calculation

Words-per-minute statistics are calculated immediately after gameplay completion.

Example:

```javascript
const wpm = Math.round((totalCorrectChars / 5) / (duration / 60))
```

Additional metrics include:

* Accuracy percentage
* Completion duration
* Challenge completion
* Earned progression XP

---

## Session Sprint History

Singleplayer gameplay maintains reactive in-session history tracking.

The sprint history sidebar stores:

* Recent challenge results
* WPM values
* Accuracy percentages
* Gameplay modes

This history updates dynamically without page reloads.

---

# 🌐 Multiplayer Matchmaking System

## Matchmaking Flow

The multiplayer architecture uses:

* Race queues
* Lobby systems
* Matchmaking polling
* Event-driven synchronization
* Route-based race transitions

Players join matchmaking using:

```javascript
callApi('/api/race_lobby.php', 'POST', {
  action: 'join'
})
```

---

## Real-Time Synchronization

Multiplayer communication is powered through:

* Pusher.js
* Websocket-style event subscriptions
* Reactive synchronization
* Live race events

Example:

```javascript
channel.bind('match-found', (data) => {
  handleMatchFound(data)
})
```

This allows:

* Synchronized race starts
* Live player updates
* Matchmaking notifications
* Race progression synchronization

---

## Match Lifecycle

The multiplayer lifecycle includes:

1. Lobby initialization
2. Matchmaking queue entry
3. Opponent synchronization
4. Race creation
5. Active race gameplay
6. Match completion
7. Result persistence

The system also handles:

* Disconnect cleanup
* Queue abandonment
* Event unbinding
* Route cleanup

---

# 🔐 Authentication & Security

## Authentication Systems

Authentication functionality includes:

* Signup
* Login
* Logout
* Protected routes
* Authenticated progression

Protected pages use:

* `AuthRequired.vue`

This component restricts unauthorized access to authenticated features.

---

## Gameplay-Inspired Authentication UX

Authentication forms use a custom typing component:

* `SpeedTextbox.vue`

Rather than standard HTML inputs.

This component tracks:

* Typing events
* Interaction timing
* Reactive input behavior

This maintains visual consistency with the gameplay experience.

---

## CAPTCHA Verification

Signup systems integrate:

* `CaptchaQuery.vue`

The CAPTCHA system provides:

* Reactive validation
* Submission gating
* Automated spam prevention

---

# 👥 Social Systems

## Friends System

The social system is implemented primarily through:

* `FriendsView.vue`

The page supports multiple interaction tabs:

* Friends
* Requests
* Find Players

---

## Player Search Functionality

Users can search for other players using reactive username search.

Example:

```javascript
const res = await callApi(`/api/friends.php?search=${encodeURIComponent(searchQuery.value)}`)
```

The search system demonstrates:

* Dynamic API querying
* Search filtering
* Asynchronous loading states
* Reactive result rendering

---

## Friend Requests

The application supports:

* Sending friend requests
* Accepting requests
* Declining requests
* Request state tracking

Friend requests are persisted through backend API calls.

Example:

```javascript
await callApi('/api/friends.php', 'POST', {
  action: 'send_request',
  to_user_id: userId
})
```

---

## Friend Management CRUD Operations

The friends system demonstrates CRUD-style interaction flows.

Supported operations include:

| Operation | Example                   |
| --------- | ------------------------- |
| Create    | Send friend request       |
| Read      | View friends and requests |
| Update    | Accept request state      |
| Delete    | Remove friend             |

Example removal operation:

```javascript
await callApi('/api/friends.php', 'POST', {
  action: 'remove',
  friend_id: userId
})
```

---

## Social Feed Systems

The application also includes:

* `SocialFeedView.vue`

This page supports community-oriented interaction systems and platform activity visibility.

---

# ✏️ CRUD Functionality

The application includes authenticated CRUD-related workflows.

## User Profile Management

Users can:

* Update profile information
* Edit account details
* Persist profile customization

---

## Friends System CRUD

The social system demonstrates CRUD architecture through:

* Friend creation
* Retrieval
* Request acceptance
* Deletion/removal

---

## Persistent Gameplay Records

Gameplay results are continuously created and persisted through:

* Challenge completion
* Multiplayer races
* Progression tracking

Results are stored through REST API endpoints.

---

# 🔍 Search, Filtering & Sorting

## Player Search

The Friends page implements reactive player search functionality.

Features include:

* Username searching
* Dynamic API querying
* Asynchronous loading states
* Filtered result rendering

---

## Leaderboard Sorting

Leaderboard systems support competitive ranking visibility.

Players are sorted according to:

* WPM
* Progression
* Gameplay performance metrics

---

## Social Data Filtering

The interface separates social data into dedicated tabs:

* Friends
* Requests
* Sent Requests
* Player Search

This improves usability and information organization.

---

# 📱 Responsive Design

The application was designed responsively using Bootstrap 5.

Responsive implementation techniques include:

* Grid-based layouts
* Breakpoint-aware columns
* Responsive spacing utilities
* Adaptive flexbox layouts
* Scalable typography

Example responsive layout:

```vue
<div class="col-12 col-md-6"></div>
```

This allows layouts to:

* Stack vertically on mobile
* Transition into multi-column layouts on larger screens

---

## Supported Device Sizes

The UI is intended to function across:

| Device Type | Layout Goal                   |
| ----------- | ----------------------------- |
| Mobile      | Vertical stacked layouts      |
| Tablet      | Hybrid responsive grid        |
| Desktop     | Full multi-column gameplay UI |

---

## Responsive Gameplay Goals

Gameplay interfaces were designed to:

* Preserve readability
* Maintain typing visibility
* Reduce layout shift
* Support smaller viewport interaction

---

# ♿ Accessibility & Usability Evaluation

## Accessibility Considerations

The application includes several accessibility-oriented design decisions.

### Keyboard Interaction

Typing gameplay is fully keyboard-driven.

The gameplay architecture emphasizes:

* Uninterrupted keyboard focus
* Reduced mouse dependency
* Continuous input interaction

---

### Readability

The interface uses:

* High-contrast neon text
* Large gameplay typography
* Visually distinct correctness states

These improve gameplay readability during high-speed interaction.

---

### Reactive Feedback

Instant visual correctness feedback improves usability by:

* Reducing user confusion
* Supporting error correction
* Improving gameplay clarity

---

### Responsive Layout Accessibility

Bootstrap responsive systems help maintain:

* Consistent spacing
* Scalable layouts
* Mobile usability

---

## Accessibility Limitations

Several limitations still exist and could be improved in future development.

Potential future improvements include:

* Improved screen reader support
* ARIA labeling
* Enhanced semantic landmarks
* Reduced motion accessibility settings
* Colorblind accessibility options

---

# 💡 Innovative Features

Several systems were intentionally designed to move beyond traditional CRUD web application structures.

## Hidden Gameplay Renderer

The stealth-input gameplay renderer separates:

* Logical text input
* Visual gameplay rendering

This enables:

* Custom cursor systems
* Terminal visuals
* Dynamic character styling
* Uninterrupted gameplay flow

This differs substantially from conventional form-based applications.

---

## Gameplay-Driven Authentication UX

Authentication systems reuse gameplay-inspired typing mechanics.

This creates:

* Visual consistency
* Thematic continuity
* A unique interaction identity

---

## Reactive Multiplayer Architecture

The multiplayer synchronization system introduces:

* Event-driven architecture
* Real-time race synchronization
* Reactive live gameplay systems

This significantly increases technical complexity compared to standard CRUD applications.

---

## Terminal/Y2K Interface Design

The interface intentionally avoids generic dashboard design patterns.

Instead, it adopts:

* Cyber-terminal aesthetics
* Radar-grid visuals
* Neon UI effects
* Retro gameplay presentation

---

# 🧠 Advanced Technical Feature

## Multiplayer Matchmaking System

The multiplayer matchmaking architecture serves as the project's primary advanced technical feature.

---

## Rationale

The multiplayer system was selected because it introduces:

* Real-time synchronization
* Reactive event handling
* Asynchronous gameplay state management
* Significantly higher frontend complexity

Compared to conventional CRUD systems, multiplayer synchronization requires:

* Live communication
* State consistency
* Coordinated gameplay events

---

## Technologies Used

The system uses:

* Pusher.js
* REST APIs
* Reactive Vue state
* Route-based synchronization

---

## Technical Complexity

The multiplayer architecture manages:

* Matchmaking queues
* Synchronized race starts
* Race lifecycle cleanup
* Event subscriptions
* Disconnect handling
* Live player updates

This demonstrates significantly more advanced frontend engineering than static page rendering.

---

# 🧱 Frontend Architecture

## Project Structure

```text
src/
│
├── assets/              # Global assets and styles
├── components/          # Reusable Vue components
│   ├── icons/           # SVG icon systems
│   └── input/           # Shared input systems
├── router/              # Vue Router configuration
├── stores/              # Pinia state stores
├── utils/               # Shared API utilities
├── views/               # Route-based application pages
├── App.vue              # Root application
└── main.js              # Application entry point
```

---

## Architectural Patterns

### Component-Driven Design

Reusable Vue components isolate:

* Gameplay systems
* UI rendering
* Input systems
* Reusable functionality

---

### Lazy-Loaded Routing

Views are dynamically imported to optimize:

* Bundle size
* Loading performance
* Scalability

---

### Shared API Layer

API requests are centralized through:

* `utils/api.js`

This standardizes:

* Fetch handling
* Credentials
* Error handling
* JSON serialization

---

### Reactive Rendering

Gameplay rendering is driven through:

* Vue refs
* Computed properties
* Event handling
* Reactive state updates

---

# 🗄 Database & Persistence Layer

## Database Technologies

The backend persistence layer is designed around a relational SQL architecture.

The schema supports:

* Authentication
* Gameplay persistence
* Multiplayer progression
* Social systems
* Achievement tracking

---

## Core Database Tables

| Table               | Purpose                         |
| ------------------- | ------------------------------- |
| `users`             | Account and profile information |
| `results`           | Gameplay history                |
| `friends`           | Social relationships            |
| `challenges`        | Challenge metadata              |
| `achievements`      | Achievement definitions         |
| `user_achievements` | Unlock progression              |
| `feed_posts`        | Social activity content         |

---

## Persistence Features

The database architecture includes:

* UUID identifiers
* Relational constraints
* Indexed lookups
* Timestamp auditing
* Persistent gameplay storage

---

# 🌐 REST API Architecture

The frontend communicates with backend services through centralized PHP REST endpoints.

---

## API Features

The API abstraction layer provides:

* JSON serialization
* Credential handling
* Standardized fetch wrappers
* Centralized error handling
* Mock backend support

---

## Example Endpoints

| Endpoint              | Purpose                      |
| --------------------- | ---------------------------- |
| `/api/challenges.php` | Generate gameplay challenges |
| `/api/results.php`    | Persist gameplay results     |
| `/api/profile.php`    | Retrieve/update profiles     |
| `/api/friends.php`    | Friend management            |
| `/api/race_lobby.php` | Multiplayer matchmaking      |
| `/api/signup.php`     | User registration            |

---

## Example API Usage

```javascript
const result = await callApi('/api/profile.php')
```

---

# 📄 Website Pages

| Route           | Component              | Purpose                         |
| --------------- | ---------------------- | ------------------------------- |
| `/`             | `HomeView.vue`         | Landing page                    |
| `/login`        | `LoginView.vue`        | User authentication             |
| `/signup`       | `SignupView.vue`       | User registration               |
| `/logout`       | `LogoutView.vue`       | Session termination             |
| `/profile`      | `ProfileView.vue`      | User progression and statistics |
| `/challenge`    | `ChallengeView.vue`    | Challenge configuration         |
| `/singleplayer` | `SingleplayerView.vue` | Solo gameplay                   |
| `/multiplayer`  | `MultiplayerView.vue`  | Matchmaking interface           |
| `/race/:uuid`   | `RaceView.vue`         | Live multiplayer race           |
| `/leaderboard`  | `LeaderboardView.vue`  | Competitive rankings            |
| `/stats`        | `StatsView.vue`        | Analytics dashboard             |
| `/achievements` | `AchievementsView.vue` | Achievement progression         |
| `/friends`      | `FriendsView.vue`      | Social systems                  |
| `/social-feed`  | `SocialFeedView.vue`   | Community activity feed         |

---

# ♻️ Reusable Components

| Component          | Purpose                      |
| ------------------ | ---------------------------- |
| `SpeedTextbox.vue` | Gameplay-inspired text input |
| `CaptchaQuery.vue` | CAPTCHA verification         |
| `AuthRequired.vue` | Route protection wrapper     |

---

# ✅ Functional Requirement Mapping

| Requirement          | Implementation                                 |
| -------------------- | ---------------------------------------------- |
| Authentication       | Login, signup, logout, protected routes        |
| Persistence          | SQL database + REST APIs                       |
| CRUD Functionality   | Profile updates, friend management             |
| Social Interaction   | Friends system, multiplayer races, social feed |
| Search Functionality | Username search in Friends system              |
| Sorting              | Competitive leaderboard ranking systems        |
| Responsive Design    | Bootstrap responsive layouts                   |
| Dynamic Frontend     | Vue reactive rendering                         |
| Advanced Feature     | Multiplayer matchmaking architecture           |
| Statistics Tracking  | WPM, accuracy, progression systems             |

---

# 🧪 Technical Requirement Mapping

| Technical Requirement | Implementation                    |
| --------------------- | --------------------------------- |
| Vue Framework         | Vue 3 Composition API             |
| State Management      | Pinia                             |
| Routing               | Vue Router                        |
| API Communication     | REST APIs                         |
| Database Persistence  | SQL relational schema             |
| Responsive Design     | Bootstrap 5                       |
| Reusable Components   | Shared Vue component architecture |
| Code Quality          | ESLint, Oxlint, Prettier          |
| Real-Time Features    | Pusher.js synchronization         |
| Dynamic Rendering     | Vue reactive state                |

---

# 📏 Coding Standards & Tooling

## Coding Conventions

The project follows consistent frontend coding conventions.

Practices include:

* Modular component organization
* Descriptive variable naming
* Composition API usage
* Reusable utility abstraction
* Centralized API handling

---

## Linting & Formatting

The development workflow uses:

* ESLint
* Oxlint
* Prettier

These tools enforce:

* Formatting consistency
* Code quality
* Maintainable architecture

---

## Project Maintainability

Maintainability is improved through:

* Component separation
* Route-based organization
* Reusable utilities
* Shared rendering systems

---

# 🚀 Deployment

The application is designed for production deployment using Vite build tooling.

---

## Production Build Process

```bash
npm run build
```

This generates an optimized production bundle.

---

## Preview Build

```bash
npm run preview
```

Allows production builds to be tested locally before deployment.

---

## Intended Deployment Environment

The project is intended to be deployed through a web hosting environment supporting:

* PHP backend APIs
* SQL database connectivity
* Static Vite frontend assets

---

# 📸 Screenshots Showcase

> Pending...

Screenshot categories:

## Gameplay

* Singleplayer gameplay
* Multiplayer matchmaking
* Active multiplayer race
* Statistics dashboard

## Authentication

* Login page
* Signup page
* CAPTCHA verification

## Social Systems

* Friends page
* Friend requests
* Social feed
* Profile pages

## Responsive Layouts

* Mobile gameplay view
* Tablet layouts
* Desktop dashboard layouts

## Progression Systems

* Achievements page
* Leaderboards
* Player statistics

---

# 🧪 Testing & Validation

## Frontend Validation

Frontend interaction systems were tested for:

* Reactive rendering stability
* Gameplay correctness
* Multiplayer synchronization
* Responsive layout behavior

---

## API Validation

REST endpoints were validated for:

* Request handling
* Response formatting
* Authentication state
* Gameplay persistence

---

## Gameplay Validation

Gameplay systems were tested for:

* Typing correctness
* WPM calculation
* Accuracy calculation
* Synchronization stability

---

# ⚙️ Getting Started

## Prerequisites

* Node.js `^20.19.0 || >=22.12.0`
* Npm

---

## Installation

```bash
git clone <repository-url>
cd hypersprint
npm install
```

---

## Development Server

```bash
npm run dev
```

Starts the Vite development server with hot module replacement.

---

## Production Build

```bash
npm run build
```

Creates an optimized production build.

---

## Preview Build

```bash
npm run preview
```

Previews the production build locally.

---

# 📜 Scripts

| Script                | Description                 |
| --------------------- | --------------------------- |
| `npm run dev`         | Start development server    |
| `npm run build`       | Build production bundle     |
| `npm run preview`     | Preview production bundle   |
| `npm run lint`        | Run all linting tasks       |
| `npm run lint:oxlint` | Run Oxlint                  |
| `npm run lint:eslint` | Run ESLint                  |
| `npm run format`      | Format files using Prettier |

---

# 🏁 Conclusion

Hypersprint demonstrates our development of a reactive multiplayer web application that combines:

* Competitive gameplay systems
* Modern frontend engineering
* Responsive design
* Social interaction systems
* Real-time synchronization

The project intentionally extends beyond traditional CRUD application architecture through:

* Multiplayer gameplay
* Event-driven synchronization
* Custom gameplay rendering
* Immersive UI design

The resulting application provides both a technically complex frontend system and a distinctive gameplay-focused user experience.
