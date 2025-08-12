<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reverb WebSocket Test</title>
</head>
<body>
<h1>Laravel Reverb WebSocket Test</h1>
<p>Open your browser console to see connection status.</p>

<script>
    const appKey = "hlhndhxldidz3hekampn"; // Your REVERB_APP_KEY
    const wsUrl = `wss://fintech.ugbanawaji.com/app/${appKey}?protocol=7&client=js&version=7.0.0&flash=false`;

    console.log("Connecting to:", wsUrl);

    let ws = new WebSocket(wsUrl);

    ws.onopen = () => {
        console.log("✅ WebSocket connected to Reverb");
    };

    ws.onerror = (error) => {
        console.error("❌ WebSocket error:", error);
    };

    ws.onclose = () => {
        console.warn("⚠️ WebSocket closed");
    };

    ws.onmessage = (msg) => {
        console.log("📩 Message received:", msg.data);
    };
</script>
</body>
</html>
