/**
 * Shared API utility for Hypersprint.
 * Handles fetch calls with proper headers and credentials.
 */

import { WORDS } from './wordList';

const USE_MOCK = true;

export async function callApi(path, method = 'GET', body = null) {
  if (USE_MOCK) {
    return handleMockRequest(path, method, body);
  }

  const url = new URL(path, window.location.origin);
  const options = {
    method,
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include'
  };

  if (body !== null) {
    options.body = JSON.stringify(body);
  }

  const res = await fetch(url, options);
  const data = await res.json();

  if (!res.ok) {
    throw new Error(data.error || 'API request failed');
  }

  return data;
}

function handleMockRequest(path, method, body) {
  // Extract query params from path if they exist
  const urlParts = path.split('?');
  const route = urlParts[0];
  const params = new URLSearchParams(urlParts[1] || '');

  console.log(`[Mock API] ${method} ${route}`, body || Object.fromEntries(params));

  return new Promise((resolve) => {
    setTimeout(() => {
      if (route === '/api/challenges.php') {
        const mode = params.get('mode') || 'words';
        const value = parseInt(params.get('value')) || 25;
        
        // Generate word pool from the full list
        let wordCount = mode === 'time' ? 300 : value; // High count for time-based tests
        let selectedWords = [];
        for (let i = 0; i < wordCount; i++) {
          selectedWords.push(WORDS[Math.floor(Math.random() * WORDS.length)]);
        }

        resolve({
          success: true,
          challenge: {
            challenge_id: 'dynamic-' + Date.now(),
            title: mode === 'time' ? `${value}s Sprint` : `${value} Words`,
            content_to_type: selectedWords.join(' '),
            difficulty: value >= 50 ? 'medium' : 'easy',
            topic: 'vocabulary',
            gamemode: mode,
            target_value: value
          }
        });
      } else if (route === '/api/results.php') {
        resolve({ success: true, result_id: 'mock-uuid-' + Date.now() });
      } else if (route === '/api/profile.php' && method !== 'POST') {
        resolve({
          success: true,
          user: {
            username: 'nedtonks', email: 'ned@example.com', profile_url: null,
            joined: '2026-01-15T10:00:00Z', level: 4.2,
            highest_wpm: 87.5, average_wpm: 72.3, average_accuracy: 96.1, challenge_count: 34
          },
          recent_results: [
            { result_id: 'r1', challenge_title: '25 Words', wpm: 87.5, accuracy: 98.0, duration: 18, completed: '2026-05-14T09:00:00Z' },
            { result_id: 'r2', challenge_title: '50 Words', wpm: 71.2, accuracy: 95.5, duration: 42, completed: '2026-05-13T14:30:00Z' }
          ]
        });
      } else {
        resolve({ success: true });
      }
    }, 300);
  });
}
