importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

firebase.initializeApp({
    apiKey: "AIzaSyDAV9XDbTCF33Uw86kIIm9r8hR_-beOhbg",
    authDomain: "valley-test-v14-8.firebaseapp.com",
    projectId: "valley-test-v14-8",
    storageBucket: "valley-test-v14-8.firebasestorage.app",
    messagingSenderId: "698827859086",
    appId: "1:698827859086:web:128d4390971873bd3215df",
    measurementId: "G-KXWVQ7XEE5"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body || '',
        icon: payload.data.icon || ''
    });
});