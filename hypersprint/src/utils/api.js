/**
 * Shared API utility for Hypersprint.
 * Handles fetch calls with proper headers and credentials.
 */

import { WORDS } from './wordList';

const USE_MOCK = false;

export async function callApi(path, method = 'GET', body = null) {
  if (USE_MOCK) {
    return handleMockRequest(path, method, body);
  }

  const basePath = import.meta.env.BASE_URL.replace(/\/$/, '');
  const url = new URL(basePath + path, window.location.origin);
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
      } else {
        resolve({ success: true });
      }
    }, 300);
  });
}
