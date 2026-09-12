// fetch('http://192.168.1.102:3000/v1/chat/completions', {
//     method: 'POST',
//     headers: { 'Content-Type': 'application/json' },
//     body: JSON.stringify({
//         model: 'qwen/qwen3.5-9b',
//         messages: [{ role: 'user', content: 'hello how are you' }],
//         temperature: 0.1,
//         max_tokens: 150
//     })
// })
//     .then(async response => console.log((await response.json()).choices[0].message))

async function fetchUserData() {
    const url = 'https://events.guten.website/api/v1/me';
    const headers = {
        'Accept': 'application/json',
        'Authorization': 'Bearer 16|yYWVFrObl7hayRMnFbdkrWbwvYMKG498quEUd5HM39c885fc'
    };

    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: headers
        });

        console.log('Status Code:', response.status);

        const data = await response.json();
        console.log('Response:', data);

        return data;
    } catch (error) {
        console.error('Error:', error);
    }
}

// Вызов функции
fetchUserData();