# Technical Design Brief: Hyper Sprint

## 1. Project Overview
[cite_start]**Hyper Sprint** is a high-performance speed typing web application designed for the **COS30043 Interface Design and Development** project[cite: 3]. [cite_start]The platform combines competitive typing mechanics with a social ecosystem, allowing users to test their skills, climb leaderboards, and interact with a community of typists[cite: 15, 19]. 

### Design Philosophy: Y2K Futuristic Maximalism
[cite_start]The application adopts a **Y2K Futuristic Maximalist** aesthetic to align with its high-speed theme[cite: 9, 10]. This includes:
* **Visual Style:** High-contrast neon color palettes (cyan, magenta, lime), chrome/metallic textures, and glassmorphic UI elements.
* [cite_start]**Layout:** High-energy, "maximalist" arrangements utilizing the **Bootstrap grid system** to maintain structural integrity while pushing visual boundaries[cite: 22].
* [cite_start]**Responsiveness:** A **mobile-first approach** ensures the complex maximalist design remains functional and fully responsive across at least three device sizes[cite: 24].

---

## 2. Technical Stack
* [cite_start]**Framework:** **Vue.js 3** utilizing a modular, component-based architecture for maintainability[cite: 7, 25].
* [cite_start]**Build Tool:** **Vite** for rapid development and optimized builds[cite: 23].
* [cite_start]**Routing:** **Vue Router** for seamless navigation between the 10+ interconnected pages[cite: 26].
* [cite_start]**State Management:** Core Vue.js features including `v-model` for input, `computed` properties for real-time WPM calculation, and `v-on` for event handling[cite: 28].
* [cite_start]**Persistence:** A **backend database** with a **RESTful API** to support CRUD operations and ensure data remains available across sessions[cite: 20, 31].
* [cite_start]**Deployment:** The application will be hosted on **Mercury**[cite: 23].

---

## 3. Page Architecture & Functional Requirements
[cite_start]The system consists of 10 interconnected pages[cite: 14]. Every page will implement the Y2K aesthetic through custom CSS while adhering to functional standards.

| Page Name | Description & Functional Implementation | Requirement Met |
| :--- | :--- | :--- |
| **1. Home** | [cite_start]High-energy dashboard with latest challenges and meaningful navigation[cite: 14]. | [cite_start]Navigation [cite: 14] |
| **2. Login** | [cite_start]Secure entry for returning users[cite: 17]. | [cite_start]Auth [cite: 17] |
| **3. Signup** | [cite_start]Registration form with rigorous **data validation**[cite: 17, 30]. | [cite_start]Forms [cite: 30] |
| **4. Profile** | [cite_start]Display of user stats and achievements; supports **editing/deleting** user data[cite: 18]. | [cite_start]CRUD [cite: 18, 31] |
| **5. Challenge** | [cite_start]The core typing engine; uses `v-bind` and `v-on` for real-time feedback[cite: 28]. | [cite_start]Interactive UI [cite: 28] |
| **6. Leaderboard** | [cite_start]Features **search and sort** functionality to filter top typists[cite: 16]. | [cite_start]Search/Sort [cite: 16] |
| **7. Results** | [cite_start]Detailed breakdown of session performance items[cite: 15]. | [cite_start]Item Details [cite: 15] |
| **8. Achievements** | [cite_start]Grid-based badge display utilizing **pagination** for large collections[cite: 29]. | [cite_start]Pagination [cite: 29] |
| **9. Friends** | [cite_start]Social management tool to find and follow other users[cite: 19]. | [cite_start]Social [cite: 19] |
| **10. Social Feed** | [cite_start]Features for **liking and voting** on friend activity[cite: 19]. | [cite_start]Interaction [cite: 19] |

---

## 4. Technical Requirements & Best Practices
* [cite_start]**Directives & Reactivity:** The application effectively uses `v-if`, `v-for`, and `v-bind` to create a reactive user interface[cite: 28].
* [cite_start]**Data Persistence:** All typing scores and user profile changes are saved to the backend database via RESTful API calls[cite: 31].
* [cite_start]**Coding Conventions:** The source code will consistently apply proper naming and indentation for readability and professionalism[cite: 32].
* [cite_start]**Validation:** All forms (Signup, Profile, Social Comments) will implement appropriate data validation[cite: 30].

---

## 5. Advanced Feature (Self-Learning)
[cite_start]One advanced Vue.js-related feature not covered in class will be implemented to significantly enhance the application[cite: 34, 35].
* **Proposed Feature:** Real-time "Ghost Race" using WebSockets or advanced Canvas-based data visualization to track typing speed trends dynamically.
* [cite_start]**Documentation:** This feature will be demonstrated in the video and detailed in the individual reports[cite: 36, 52].

---

## [cite_start]6. Deliverables [cite: 56]
1.  [cite_start]**Source Code:** Fully documented Vite/Vue.js project[cite: 57].
2.  [cite_start]**Mercury URL:** Link to the live, responsive application[cite: 58].
3.  [cite_start]**Group Report:** Evaluation of usability, accessibility, and unique features (max 12 pages)[cite: 37, 42].
4.  [cite_start]**Demonstration Video:** 5-10 minute continuous take showcasing all functionalities[cite: 44, 47].
5.  [cite_start]**Individual Reports:** Summary of contributions and technical challenges (max 5 pages)[cite: 50, 51].