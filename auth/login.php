<?php require_once '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-16">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="py-4 px-6 bg-green-600 text-white text-center">
            <h2 class="text-2xl font-bold">Login to Your Account</h2>
        </div>
        
        <div class="py-4 px-6">
            <div id="login-error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 hidden"></div>
            
            <form id="login-form">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" id="username" name="username" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Sign In
                    </button>
                    <a href="register.php" class="inline-block align-baseline font-bold text-sm text-green-600 hover:text-green-800">
                        Create an account
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        // Validate input
        if (!username || !password) {
            loginError.textContent = 'Username and password are required';
            loginError.classList.remove('hidden');
            return;
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        
        // Send login request
        fetch('../api/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                loginError.textContent = data.message;
                loginError.classList.remove('hidden');
            } else {
                // Redirect to home page on success
                window.location.href = '../index.php';
            }
        })
        .catch(error => {
            loginError.textContent = 'An error occurred. Please try again.';
            loginError.classList.remove('hidden');
            console.error('Login error:', error);
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>