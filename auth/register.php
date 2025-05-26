<?php require_once '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-16">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="py-4 px-6 bg-green-600 text-white text-center">
            <h2 class="text-2xl font-bold">Create an Account</h2>
        </div>
        
        <div class="py-4 px-6">
            <div id="register-error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 hidden"></div>
            <div id="register-success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 hidden">
                Registration successful! <a href="login.php" class="text-green-600 hover:underline">Login now</a>
            </div>
            
            <form id="register-form">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" id="username" name="username" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" id="email" name="email" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-6">
                    <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Register
                    </button>
                    <a href="login.php" class="inline-block align-baseline font-bold text-sm text-green-600 hover:text-green-800">
                        Already have an account?
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    const registerError = document.getElementById('register-error');
    const registerSuccess = document.getElementById('register-success');
    
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Hide any previous messages
        registerError.classList.add('hidden');
        registerSuccess.classList.add('hidden');
        
        // Validate input
        if (!username || !email || !password || !confirmPassword) {
            registerError.textContent = 'All fields are required';
            registerError.classList.remove('hidden');
            return;
        }
        
        if (password !== confirmPassword) {
            registerError.textContent = 'Passwords do not match';
            registerError.classList.remove('hidden');
            return;
        }
        
        if (password.length < 6) {
            registerError.textContent = 'Password must be at least 6 characters long';
            registerError.classList.remove('hidden');
            return;
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('username', username);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('confirm_password', confirmPassword);
        
        // Send registration request
        fetch('../api/register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                registerError.textContent = data.message;
                registerError.classList.remove('hidden');
            } else {
                // Show success message
                registerSuccess.classList.remove('hidden');
                // Clear the form
                registerForm.reset();
            }
        })
        .catch(error => {
            registerError.textContent = 'An error occurred. Please try again.';
            registerError.classList.remove('hidden');
            console.error('Registration error:', error);
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>