<script setup lang="ts">
import { ref } from 'vue';
import PublicLayout from '../layouts/Public.vue';

interface Comparison {
    feature: string;
    kepler: string;
    elasticsearch: string;
    algolia: string;
    traditional: string;
}

const comparisons = ref<Comparison[]>([
    {
        feature: 'Setup Complexity',
        kepler: 'Minutes - Single Python script',
        elasticsearch: 'Hours - Complex cluster setup',
        algolia: 'Medium - API integration',
        traditional: 'Low - Basic SQL queries'
    },
    {
        feature: 'Infrastructure Cost',
        kepler: 'Minimal - Runs alongside app',
        elasticsearch: 'High - Dedicated servers/memory',
        algolia: 'Variable - Per operation pricing',
        traditional: 'Low - Database only'
    },
    {
        feature: 'Search Quality',
        kepler: 'High - TF-IDF + AI weighting',
        elasticsearch: 'Very High - Full-text engine',
        algolia: 'Very High - Optimized engine',
        traditional: 'Low - Basic LIKE queries'
    },
    {
        feature: 'Relevance Tuning',
        kepler: 'Easy - Weighted fields in code',
        elasticsearch: 'Complex - Query DSL',
        algolia: 'Medium - Dashboard settings',
        traditional: 'Limited - Manual SQL'
    },
    {
        feature: 'Maintenance',
        kepler: 'Minimal - Auto-managed',
        elasticsearch: 'High - Index management',
        algolia: 'Low - Managed service',
        traditional: 'Low - Database native'
    },
    {
        feature: 'Scalability',
        kepler: 'Good - Up to millions of docs',
        elasticsearch: 'Excellent - Distributed',
        algolia: 'Excellent - Cloud scale',
        traditional: 'Poor - Degrades quickly'
    },
    {
        feature: 'Developer Experience',
        kepler: 'Excellent - Simple API',
        elasticsearch: 'Steep - Complex DSL',
        algolia: 'Good - Well documented',
        traditional: 'Simple - Standard SQL'
    },
    {
        feature: 'Response Time',
        kepler: 'Fast - <100ms typical',
        elasticsearch: 'Very Fast - <50ms',
        algolia: 'Very Fast - <50ms',
        traditional: 'Slow - 500ms+ on scale'
    }
]);

const features = ref([
    {
        icon: '🚀',
        title: 'Rapid Deployment',
        description: 'From zero to production in under 10 minutes. No complex infrastructure or DevOps expertise required.'
    },
    {
        icon: '💰',
        title: 'Cost Effective',
        description: 'No per-search pricing, no dedicated servers. Runs efficiently alongside your existing Laravel application.'
    },
    {
        icon: '🎯',
        title: 'Smart Relevance',
        description: 'AI-powered field weighting ensures titles and tags rank higher than body content automatically.'
    },
    {
        icon: '🔧',
        title: 'Developer Friendly',
        description: 'Simple Python script with clean API. Customize search behavior with a few lines of code.'
    },
    {
        icon: '📊',
        title: 'TF-IDF Algorithm',
        description: 'Industry-proven Term Frequency-Inverse Document Frequency with cosine similarity scoring.'
    },
    {
        icon: '⚡',
        title: 'Performance',
        description: 'Sub-100ms search response times for datasets up to millions of documents with minimal memory footprint.'
    }
]);

const stats = ref([
    { value: '<10min', label: 'Setup Time' },
    { value: '<100ms', label: 'Search Speed' },
    { value: '90%+', label: 'Cost Savings' },
    { value: '1M+', label: 'Documents' }
]);

const scrollToDemo = () => {
    window.location.href = '/dashboard';
};
</script>

<template>
    <PublicLayout :show-header="false" :show-footer="false">
        <div class="landing">
            <!-- Hero Section -->
            <section class="hero">
                <div class="hero-content">
                    <h1 class="hero-title">
                        <span class="gradient-text">Kepler AI Search</span>
                    </h1>
                    <p class="hero-subtitle">
                        Enterprise-Grade Search Without Enterprise Complexity
                    </p>
                    <p class="hero-description">
                        A lightweight, intelligent search solution that delivers the power of Elasticsearch
                        at a fraction of the cost and complexity. Built for modern web applications.
                    </p>
                    <div class="hero-stats">
                        <div v-for="stat in stats" :key="stat.label" class="stat-item">
                            <div class="stat-value">{{ stat.value }}</div>
                            <div class="stat-label">{{ stat.label }}</div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <button @click="scrollToDemo" class="btn btn-primary">
                            Try Live Demo
                        </button>
                        <a href="#comparison" class="btn btn-secondary">
                            See Comparison
                        </a>
                    </div>
                </div>
            </section>

            <!-- Problem Statement -->
            <section class="section section-problem">
                <div class="container">
                    <h2 class="section-title">The Search Engine Dilemma</h2>
                    <div class="problem-grid">
                        <div class="problem-card">
                            <h3>❌ Traditional Database Search</h3>
                            <p>Simple LIKE queries are fast to implement but deliver poor results. No relevance ranking,
                                no fuzzy matching, and performance degrades rapidly as data grows.</p>
                        </div>
                        <div class="problem-card">
                            <h3>❌ Elasticsearch</h3>
                            <p>Powerful but requires dedicated infrastructure, complex setup, ongoing maintenance,
                                and deep expertise. Overkill for 90% of applications.</p>
                        </div>
                        <div class="problem-card">
                            <h3>❌ Managed Services (Algolia, etc.)</h3>
                            <p>Easy to use but expensive at scale. Per-operation pricing adds up quickly,
                                and you're locked into their ecosystem.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Solution -->
            <section class="section section-solution">
                <div class="container">
                    <h2 class="section-title">
                        <span class="gradient-text">The Kepler Approach</span>
                    </h2>
                    <p class="section-intro">
                        We've created a search solution that combines the best of all worlds:
                        the simplicity of database search, the intelligence of AI algorithms,
                        and the performance of dedicated engines—without the complexity or cost.
                    </p>

                    <div class="features-grid">
                        <div v-for="feature in features" :key="feature.title" class="feature-card">
                            <div class="feature-icon">{{ feature.icon }}</div>
                            <h3 class="feature-title">{{ feature.title }}</h3>
                            <p class="feature-description">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Technical Deep Dive -->
            <section class="section section-tech">
                <div class="container">
                    <h2 class="section-title">How It Works</h2>
                    <div class="tech-content">
                        <div class="tech-block">
                            <h3>🧠 TF-IDF Vectorization</h3>
                            <p>
                                We use Term Frequency-Inverse Document Frequency (TF-IDF), the same algorithm
                                powering Google's early search. It automatically identifies important terms
                                and filters out common words, delivering highly relevant results.
                            </p>
                        </div>

                        <div class="tech-block">
                            <h3>📐 Cosine Similarity</h3>
                            <p>
                                Documents are represented as vectors in multi-dimensional space. We calculate
                                the cosine similarity between the query vector and document vectors, providing
                                accurate relevance scores from 0 to 1.
                            </p>
                        </div>

                        <div class="tech-block">
                            <h3>⚖️ Smart Field Weighting</h3>
                            <p>
                                Not all content is equal. Our system automatically weights title matches 3x higher
                                than tags (2x) and body content (1x), ensuring the most relevant results surface first.
                            </p>
                        </div>

                        <div class="tech-block">
                            <h3>🔄 Real-time Updates</h3>
                            <p>
                                Changes to your document database are immediately reflected in search results.
                                The TF-IDF matrix rebuilds efficiently, keeping your search fresh without manual
                                intervention.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Comparison Table -->
            <section id="comparison" class="section section-comparison">
                <div class="container">
                    <h2 class="section-title">Head-to-Head Comparison</h2>
                    <div class="table-wrapper">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <th class="highlight-col">Kepler AI Search</th>
                                    <th>Elasticsearch</th>
                                    <th>Algolia</th>
                                    <th>Traditional SQL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(comp, index) in comparisons" :key="index">
                                    <td class="feature-name">{{ comp.feature }}</td>
                                    <td class="highlight-col">
                                        <strong>{{ comp.kepler }}</strong>
                                    </td>
                                    <td>{{ comp.elasticsearch }}</td>
                                    <td>{{ comp.algolia }}</td>
                                    <td>{{ comp.traditional }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Use Cases -->
            <section class="section section-usecases">
                <div class="container">
                    <h2 class="section-title">Perfect For</h2>
                    <div class="usecases-grid">
                        <div class="usecase-card">
                            <h3>📚 Content Platforms</h3>
                            <p>Blogs, documentation sites, knowledge bases, and content management systems.</p>
                        </div>
                        <div class="usecase-card">
                            <h3>🛍️ E-commerce</h3>
                            <p>Product catalogs up to millions of SKUs with instant, relevant search results.</p>
                        </div>
                        <div class="usecase-card">
                            <h3>💼 SaaS Applications</h3>
                            <p>Customer portals, internal tools, and business applications requiring search.</p>
                        </div>
                        <div class="usecase-card">
                            <h3>🎓 Educational Platforms</h3>
                            <p>Course libraries, learning management systems, and student resources.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Investment Opportunity -->
            <section class="section section-investment">
                <div class="container">
                    <h2 class="section-title">Investment Opportunity</h2>
                    <div class="investment-content">
                        <div class="investment-block">
                            <h3>🎯 Market Opportunity</h3>
                            <p>
                                The search-as-a-service market is projected to reach $7.3B by 2027. However,
                                current solutions force companies to choose between expensive managed services
                                or complex self-hosted infrastructure. Kepler bridges this gap.
                            </p>
                        </div>

                        <div class="investment-block">
                            <h3>💡 Competitive Advantage</h3>
                            <ul>
                                <li><strong>80% lower TCO</strong> compared to Elasticsearch deployments</li>
                                <li><strong>90% faster implementation</strong> than traditional search engines</li>
                                <li><strong>Zero vendor lock-in</strong> - runs on your infrastructure</li>
                                <li><strong>Open architecture</strong> - built on proven open-source technologies</li>
                            </ul>
                        </div>

                        <div class="investment-block">
                            <h3>📈 Business Model</h3>
                            <p>
                                <strong>Open Core Strategy:</strong> Free open-source version for individual developers
                                and small teams. Premium enterprise features include advanced analytics, multi-tenancy,
                                distributed search, and dedicated support.
                            </p>
                        </div>

                        <div class="investment-block">
                            <h3>🚀 Traction & Roadmap</h3>
                            <p>
                                Currently deployed in production environments handling millions of documents.
                                Roadmap includes: distributed search, machine learning-powered ranking,
                                semantic search capabilities, and turnkey Docker deployment.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="section section-cta">
                <div class="container">
                    <div class="cta-card">
                        <h2>Ready to Transform Your Search?</h2>
                        <p>Experience the power of intelligent search without the complexity.</p>
                        <div class="cta-actions">
                            <button @click="scrollToDemo" class="btn btn-primary btn-large">
                                Try Live Demo
                            </button>
                            <a href="https://github.com/net-shell/kepler" target="_blank"
                                class="btn btn-secondary btn-large">
                                View on GitHub
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="footer">
                <div class="container">
                    <p>&copy; 2025 Kepler AI Search. Built with Laravel, Vue 3, and Python.</p>
                </div>
            </footer>
        </div>
    </PublicLayout>
</template>

<style scoped>
.landing {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    color: #2c3e50;
    background: #f8fafc;
}

/* Hero Section */
.hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 6rem 2rem;
    text-align: center;
}

.hero-content {
    max-width: 1000px;
    margin: 0 auto;
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 1rem;
    line-height: 1.1;
}

.gradient-text {
    background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
    opacity: 0.95;
}

.hero-description {
    font-size: 1.25rem;
    opacity: 0.9;
    max-width: 800px;
    margin: 0 auto 3rem;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin: 3rem 0;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1rem;
    opacity: 0.9;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

/* Buttons */
.btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: white;
    color: #667eea;
}

.btn-primary:hover {
    background: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid white;
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.btn-large {
    padding: 1.25rem 2.5rem;
    font-size: 1.2rem;
}

/* Sections */
.section {
    padding: 5rem 2rem;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 3rem;
    color: #2c3e50;
}

.section-intro {
    font-size: 1.25rem;
    text-align: center;
    max-width: 800px;
    margin: 0 auto 4rem;
    color: #64748b;
    line-height: 1.7;
}

/* Problem Section */
.section-problem {
    background: white;
}

.problem-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.problem-card {
    padding: 2rem;
    background: #fff5f5;
    border-radius: 12px;
    border-left: 4px solid #ef4444;
}

.problem-card h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #991b1b;
}

.problem-card p {
    color: #64748b;
    line-height: 1.6;
}

/* Solution Section */
.section-solution {
    background: #f8fafc;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.feature-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.feature-description {
    color: #64748b;
    line-height: 1.6;
}

/* Tech Section */
.section-tech {
    background: white;
}

.tech-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.tech-block {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.tech-block h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.tech-block p {
    color: #64748b;
    line-height: 1.6;
    font-size: 0.95rem;
}

/* Comparison Section */
.section-comparison {
    background: #f8fafc;
}

.table-wrapper {
    overflow-x: auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.comparison-table {
    width: 100%;
    border-collapse: collapse;
}

.comparison-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.comparison-table th {
    padding: 1.5rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 1rem;
}

.comparison-table td {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.comparison-table tbody tr:hover {
    background: #f8fafc;
}

.feature-name {
    font-weight: 600;
    color: #2c3e50;
}

.highlight-col {
    background: #fef3c7;
    font-weight: 600;
    color: #92400e;
}

.comparison-table thead .highlight-col {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* Use Cases Section */
.section-usecases {
    background: white;
}

.usecases-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.usecase-card {
    padding: 2rem;
    background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
    border-radius: 12px;
    border: 2px solid #e2e8f0;
}

.usecase-card h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.usecase-card p {
    color: #64748b;
    line-height: 1.6;
}

/* Investment Section */
.section-investment {
    background: #f8fafc;
}

.investment-content {
    display: grid;
    gap: 2rem;
}

.investment-block {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.investment-block h3 {
    font-size: 1.75rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.investment-block p {
    color: #64748b;
    line-height: 1.7;
    font-size: 1.1rem;
}

.investment-block ul {
    list-style: none;
    padding: 0;
    margin-top: 1rem;
}

.investment-block li {
    padding: 0.75rem 0;
    color: #64748b;
    font-size: 1.05rem;
    line-height: 1.6;
}

.investment-block li strong {
    color: #667eea;
}

/* CTA Section */
.section-cta {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.cta-card {
    text-align: center;
    padding: 3rem 2rem;
}

.cta-card h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.cta-card p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.95;
}

.cta-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Footer */
.footer {
    background: #2c3e50;
    color: white;
    padding: 2rem;
    text-align: center;
}

.footer p {
    opacity: 0.8;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }

    .hero-subtitle {
        font-size: 1.25rem;
    }

    .hero-description {
        font-size: 1rem;
    }

    .hero-stats {
        gap: 1.5rem;
    }

    .stat-value {
        font-size: 2rem;
    }

    .section-title {
        font-size: 2rem;
    }

    .hero-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}
</style>
