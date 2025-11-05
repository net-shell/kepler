.PHONY: help install dev build serve migrate seed clean demo test

help:
	@echo "AI Search - Available Commands"
	@echo "=============================="
	@echo ""
	@echo "Setup:"
	@echo "  make install    - Install all dependencies"
	@echo "  make migrate    - Run database migrations"
	@echo "  make seed       - Seed database with sample data"
	@echo ""
	@echo "Development:"
	@echo "  make dev        - Start development servers"
	@echo "  make serve      - Start Laravel server only"
	@echo "  make build      - Build production assets"
	@echo ""
	@echo "Data:"
	@echo "  make demo       - Load demo data via API"
	@echo "  make clean      - Clean build files"
	@echo ""
	@echo "Testing:"
	@echo "  make test       - Test Python script"
	@echo ""

install:
	@echo "📦 Installing dependencies..."
	composer install
	npm install
	pip3 install -r requirements.txt
	@echo "✅ Dependencies installed"

migrate:
	@echo "🔄 Running migrations..."
	touch database/database.sqlite
	php artisan migrate
	@echo "✅ Migrations complete"

seed:
	@echo "🌱 Seeding database..."
	php artisan db:seed
	@echo "✅ Database seeded"

dev:
	@echo "🚀 Starting development servers..."
	@echo "Laravel: http://localhost:8000"
	@echo "Press Ctrl+C to stop"
	@make -j2 serve vite

serve:
	php artisan serve

vite:
	npm run dev

build:
	@echo "🏗️  Building production assets..."
	npm run build
	@echo "✅ Build complete"

demo:
	@echo "📊 Loading demo data..."
	python3 scripts/demo_data_loader.py
	@echo "✅ Demo data loaded"

clean:
	@echo "🧹 Cleaning build files..."
	rm -rf public/build
	rm -rf node_modules/.vite
	@echo "✅ Clean complete"

test:
	@echo "🧪 Testing Python script..."
	python3 scripts/ai_search_api.py
