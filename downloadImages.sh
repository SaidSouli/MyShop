#!/bin/bash


# Download some placeholder images
cd public/uploads/products
# Download 10 placeholder images
for i in {1..10}; do
    curl -o "product$i.jpg" "https://picsum.photos/id/$((RANDOM % 200))/200/200"
done