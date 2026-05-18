# Frontend API Calls Guide for Hypersprint

This guide explains how the Vue frontend should call the backend PHP API.

## General approach

The frontend should use `fetch()` to talk to API endpoints under `/api/`. The responses are JSON. Do not send the database password from the frontend.

## Example fetch function

```javascript
async function apiPost(path, body) {
  const res = await fetch(path, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(body),
    credentials: 'include'
  });
  return res.json();
}

async function apiGet(path) {
  const res = await fetch(path, {
    method: 'GET',
    credentials: 'include'
  });
  return res.json();
}
```

## Login example

```javascript
const response = await apiPost('/api/login.php', {
  username: loginUsername,
  password: loginPassword
});

if (response.success) {
  // user is logged in
} else {
  // show error message
}
```

## Register example

```javascript
const response = await apiPost('/api/register.php', {
  username: signupUsername,
  email: signupEmail,
  password: signupPassword
});
```

## Fetching challenges

```javascript
const response = await apiGet('/api/challenges.php');
const challenges = response.challenges;
```

## Submitting a result

```javascript
const response = await apiPost('/api/results.php', {
  challenge_uuid: selectedChallengeUuid,
  wpm: completedWpm,
  accuracy: completedAccuracy,
  time_taken: durationSeconds,
  experience: xpEarned
});
```

## Logout example

```javascript
const response = await apiPost('/api/logout.php', {});
```

## Notes for Vue

- Keep API calls in a shared module or composable, not scattered everywhere.
- Store user auth state in Pinia or component state.
- Use `credentials: 'include'` if the backend uses PHP sessions.
- Handle errors gracefully: show a message when `response.error` exists.
- Do not expose secrets in frontend code.

## Template for calling an API endpoint

```javascript
async function callApi(path, method = 'GET', body = null) {
  const options = {
    method,
    headers: {
      'Content-Type': 'application/json'
    },
    credentials: 'include'
  };

  if (body !== null) {
    options.body = JSON.stringify(body);
  }

  const res = await fetch(path, options);
  const data = await res.json();

  if (!res.ok) {
    throw new Error(data.error || 'API request failed');
  }

  return data;
}
```

Use the template above for every API call to keep the frontend consistent.
