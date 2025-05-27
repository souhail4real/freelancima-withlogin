/**
 * FreeLanci.ma - Simplified Main Application
 * Author: souhail4real
 * Updated: 2025-05-26 23:51:55
 */

// State variables
let freelancerData = {};
let currentCategory = 'web-development';
let currentPage = 1;
const freelancersPerPage = 28;
const metaData = { lastUpdated: "2025-05-26 23:51:55", updatedBy: "souhail4real" };

// Simplified categories object with most important keywords
const categories = {
    'web-development': ['web', 'javascript', 'react', 'vue', 'angular', 'node', 'php', 'html', 'css'],
    'mobile-development': ['mobile', 'android', 'ios', 'flutter', 'react native', 'swift'],
    'data-science-ml': ['data', 'machine learning', 'ai', 'python', 'tensorflow', 'data analysis'],
    'cybersecurity': ['security', 'cyber', 'ethical hacking', 'penetration testing'],
    'cloud-devops': ['cloud', 'aws', 'azure', 'devops', 'docker', 'kubernetes']
};

// Data loading
async function loadFreelancerData() {
    try {
        const response = await fetch('api/get_freelancers.php?action=all');
        if (!response.ok) throw new Error(`Failed: ${response.status}`);
        
        const data = await response.json();
        
        // Update metadata and store data
        if (data.metadata) {
            metaData.lastUpdated = data.metadata.last_updated;
            metaData.updatedBy = data.metadata.updated_by;
        }
        
        freelancerData = data.categories;
        return data.categories;
    } catch (error) {
        console.error('Error loading data:', error);
        return {};
    }
}

// Render functions
function renderFreelancerCard(freelancer) {
    return `
        <div class="freelancer-card bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
            <a href="${freelancer.profile_link}" target="_blank" class="block">
                <div class="relative">
                    <img src="${freelancer.profile_image}" alt="${freelancer.username}" class="w-full h-48 object-cover">
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                        <h3 class="text-white font-semibold text-xl">${freelancer.username}</h3>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-center mb-2">
                        <div class="flex text-yellow-400">${renderStars(freelancer.rating)}</div>
                        <span class="ml-2 text-gray-600">${freelancer.rating} (${freelancer.reviews} reviews)</span>
                    </div>
                    <p class="text-gray-700">${freelancer.short_description}</p>
                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-green-600 font-semibold">Starting at $${freelancer.price}</span>
                        <button class="text-green-600 hover:text-green-700" onclick="event.stopPropagation(); toggleFavorite(this);">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </div>
            </a>
        </div>
    `;
}

function renderStars(rating) {
    const stars = [];
    const fullStars = Math.floor(parseFloat(rating));
    const hasHalfStar = parseFloat(rating) % 1 >= 0.5;
    
    for (let i = 0; i < fullStars; i++) stars.push('<i class="fas fa-star"></i>');
    if (hasHalfStar) stars.push('<i class="fas fa-star-half-alt"></i>');
    
    const emptyStars = 5 - stars.length;
    for (let i = 0; i < emptyStars; i++) stars.push('<i class="far fa-star"></i>');
    
    return stars.join('');
}

function renderPaginationControls(totalFreelancers, currentPage) {
    const container = document.getElementById('pagination-container');
    if (!container) return;
    
    const totalPages = Math.ceil(totalFreelancers / freelancersPerPage);
    container.innerHTML = '';

    if (totalPages > 1) {
        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            button.className = `pagination-button ${i === currentPage ? 'active' : ''}`;
            button.addEventListener('click', () => renderFreelancers(currentCategory, i));
            container.appendChild(button);
        }
    }
}

// Main function to render freelancers
async function renderFreelancers(category, page = 1) {
    const container = document.getElementById('freelancer-container');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    if (!container) return;
    
    if (loadingSpinner) loadingSpinner.classList.remove('hidden');
    container.innerHTML = '';
    
    try {
        // Load data if needed
        if (!freelancerData[category] || freelancerData[category].length === 0) {
            const response = await fetch(`api/get_freelancers.php?action=category&category=${category}`);
            if (!response.ok) throw new Error(`Failed: ${response.status}`);
            
            const data = await response.json();
            
            if (data.metadata) {
                metaData.lastUpdated = data.metadata.last_updated;
                metaData.updatedBy = data.metadata.updated_by;
            }
            
            if (data.categories && data.categories[category]) {
                freelancerData[category] = data.categories[category];
            }
        }
        
        // Get freelancers and update state
        const freelancers = freelancerData[category] || [];
        currentCategory = category;
        currentPage = page;

        if (freelancers.length === 0) {
            container.innerHTML = '<p class="text-center col-span-full py-8">No freelancers found in this category.</p>';
        } else {
            // Paginate and render
            const startIndex = (page - 1) * freelancersPerPage;
            const endIndex = startIndex + freelancersPerPage;
            const paginatedFreelancers = freelancers.slice(startIndex, endIndex);

            container.innerHTML = paginatedFreelancers.map(freelancer => renderFreelancerCard(freelancer)).join('');
            renderPaginationControls(freelancers.length, page);
        }

        updateMetadataDisplay();
    } catch (error) {
        console.error('Error rendering freelancers:', error);
        container.innerHTML = '<p class="text-center text-red-500 py-8">Error loading data. Please try again later.</p>';
    } finally {
        if (loadingSpinner) loadingSpinner.classList.add('hidden');
    }
}

// Helper functions
function updateMetadataDisplay() {
    const metadataElement = document.getElementById('metadata-info');
    if (metadataElement) {
        metadataElement.innerHTML = `<p class="text-xs text-gray-500">Last updated: ${metaData.lastUpdated} by ${metaData.updatedBy}</p>`;
    }
}

function toggleFavorite(button) {
    const icon = button.querySelector('i');
    icon.classList.toggle('far');
    icon.classList.toggle('fas');
}

// Search functions
async function searchFreelancers(query) {
    if (!query || query.trim() === '') return [];
    
    query = query.toLowerCase().trim();
    
    try {
        const response = await fetch(`api/get_freelancers.php?action=search&search=${encodeURIComponent(query)}`);
        if (!response.ok) throw new Error(`Search failed: ${response.status}`);
        
        const data = await response.json();
        
        // Process results
        const results = [];
        for (const [category, freelancers] of Object.entries(data.categories)) {
            for (const freelancer of freelancers) {
                results.push({...freelancer, category});
            }
        }
        
        return results;
    } catch (error) {
        console.error('Search error:', error);
        
        // Client-side fallback
        if (Object.keys(freelancerData).length > 0) {
            const results = [];
            
            for (const [category, freelancers] of Object.entries(freelancerData)) {
                for (const freelancer of freelancers) {
                    if (
                        freelancer.username.toLowerCase().includes(query) ||
                        freelancer.short_description.toLowerCase().includes(query)
                    ) {
                        results.push({...freelancer, category});
                    }
                }
            }
            
            return results;
        }
        
        return [];
    }
}

function displaySearchResults(results) {
    const resultsContainer = document.getElementById('search-results-container');
    const searchResultsSection = document.getElementById('search-results');
    
    if (!resultsContainer || !searchResultsSection) return;
    
    resultsContainer.innerHTML = results.length === 0 ?
        '<p class="text-center py-8">No freelancers found matching your search.</p>' :
        results.map(freelancer => renderFreelancerCard(freelancer)).join('');
    
    searchResultsSection.classList.remove('hidden');
    document.getElementById('freelancer-container').classList.add('hidden');
}

function clearSearch() {
    const searchResults = document.getElementById('search-results');
    const freelancerContainer = document.getElementById('freelancer-container');
    const searchInput = document.getElementById('hero-search-input');
    
    if (searchResults) searchResults.classList.add('hidden');
    if (freelancerContainer) freelancerContainer.classList.remove('hidden');
    if (searchInput) searchInput.value = '';
}

// Filter functions
function applyAdvancedFilters() {
    const minPrice = document.getElementById('price-min')?.value;
    const maxPrice = document.getElementById('price-max')?.value;
    const category = document.getElementById('category-filter')?.value;
    const skillsInput = document.getElementById('skills-filter')?.value || '';
    const skills = skillsInput.toLowerCase().split(',').map(s => s.trim()).filter(s => s);
    
    // Set category if selected
    if (category) {
        currentCategory = category;
        document.querySelectorAll('.category-card').forEach(card => {
            card.classList.toggle('selected-category', card.getAttribute('data-category') === category);
        });
    }
    
    // Get all relevant freelancers
    let allFreelancers = [];
    if (category) {
        allFreelancers = freelancerData[category] || [];
    } else {
        Object.values(freelancerData).forEach(categoryFreelancers => {
            allFreelancers = [...allFreelancers, ...categoryFreelancers];
        });
    }
    
    // Apply filters
    const filteredFreelancers = allFreelancers.filter(freelancer => {
        const price = parseInt(freelancer.price);
        
        // Price filters
        if (minPrice && price < parseInt(minPrice)) return false;
        if (maxPrice && price > parseInt(maxPrice)) return false;
        
        // Skills filter
        if (skills.length > 0) {
            const description = freelancer.short_description.toLowerCase();
            const hasMatchingSkill = skills.some(skill => description.includes(skill));
            if (!hasMatchingSkill) return false;
        }
        
        return true;
    });
    
    // Display results
    const container = document.getElementById('freelancer-container');
    if (!container) return;
    
    document.getElementById('pagination-container').innerHTML = '';
    
    if (filteredFreelancers.length === 0) {
        container.innerHTML = '<p class="text-center py-8">No freelancers match your filters. Try adjusting your criteria.</p>';
    } else {
        container.innerHTML = filteredFreelancers.map(freelancer => renderFreelancerCard(freelancer)).join('');
    }
    
    container.classList.remove('hidden');
    document.getElementById('search-results')?.classList.add('hidden');
}

// Initialization
function initUI() {
    // 1. Category selection
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.category-card').forEach(c => c.classList.remove('selected-category'));
            this.classList.add('selected-category');
            
            const category = this.getAttribute('data-category');
            clearSearch();
            renderFreelancers(category);
        });
    });
    
    // 2. Search functionality
    const searchInput = document.getElementById('hero-search-input');
    const searchButton = document.getElementById('hero-search-button');
    
    if (searchButton) {
        searchButton.addEventListener('click', () => performSearch(searchInput.value));
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') performSearch(searchInput.value);
        });
    }
    
    document.getElementById('clear-search')?.addEventListener('click', clearSearch);
    
    // 3. Filter toggles
    const toggleFilters = document.getElementById('toggle-filters');
    const advancedFilters = document.getElementById('advanced-filters');
    
    if (toggleFilters && advancedFilters) {
        toggleFilters.addEventListener('click', () => {
            const isVisible = advancedFilters.style.display === 'block';
            advancedFilters.style.display = isVisible ? 'none' : 'block';
            
            const icon = toggleFilters.querySelector('.fa-chevron-down, .fa-chevron-up');
            if (icon) {
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            }
        });
    }
    
    // 4. Filter buttons
    document.getElementById('apply-filters')?.addEventListener('click', applyAdvancedFilters);
    
    document.getElementById('clear-filters')?.addEventListener('click', () => {
        document.getElementById('price-min').value = '';
        document.getElementById('price-max').value = '';
        document.getElementById('category-filter').value = '';
        document.getElementById('skills-filter').value = '';
        renderFreelancers(currentCategory);
    });
}

// Search execution
async function performSearch(query) {
    const loadingSpinner = document.getElementById('loading-spinner');
    if (loadingSpinner) loadingSpinner.classList.remove('hidden');
    
    try {
        if (Object.keys(freelancerData).length === 0) await loadFreelancerData();
        const results = await searchFreelancers(query);
        displaySearchResults(results);
    } catch (error) {
        console.error('Search error:', error);
    } finally {
        if (loadingSpinner) loadingSpinner.classList.add('hidden');
    }
    
    document.getElementById('search-results')?.scrollIntoView({ behavior: 'smooth' });
}

// Initialize app
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await loadFreelancerData();
        initUI();
        renderFreelancers('web-development');
    } catch (error) {
        console.error('Initialization error:', error);
    }
});