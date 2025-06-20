<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductObserver {
    /**
     * Static property to track images to delete by product ID
     */
    private static array $imagesToDelete = [];
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updating(Product $product): void {
        // Store the original images before the update
        if ($product->isDirty('images')) {
            $originalImages = $product->getOriginal('images');
            $newImages = $product->images;

            // Store the images to be deleted using product ID as key
            $imagesToDelete = $this->getImagesToDelete($originalImages, $newImages);
            if (!empty($imagesToDelete)) {
                self::$imagesToDelete[$product->id] = $imagesToDelete;
            }
        }
    }

    public function updated(Product $product): void {
        // Delete the images that were marked for deletion
        if (isset(self::$imagesToDelete[$product->id])) {
            $this->deleteImageFiles(self::$imagesToDelete[$product->id]);
            unset(self::$imagesToDelete[$product->id]);
        }
    }
    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void {
        // Delete all image files when product is deleted
        if ($product->images && !empty($product->images)) {
            $this->deleteImageFiles($product->images);
        }
    }
    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void {
        //
    }
    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void {
        //
    }
    /**
     * Normalize image array from various formats
     */
    private function normalizeImageArray($images): array {
        if (is_null($images) || $images === '') {
            return [];
        }

        if (is_string($images)) {
            $decoded = json_decode($images, true);
            return is_array($decoded) ? array_filter($decoded) : [];
        }

        if (is_array($images)) {
            return array_filter($images);
        }

        return [];
    }
    /**
     * Get images that should be deleted
     */
    private function getImagesToDelete($originalImages, $newImages): array {
        // Normalize both arrays
        $originalArray = $this->normalizeImageArray($originalImages);
        $newArray = $this->normalizeImageArray($newImages);

        // If no original images, nothing to delete
        if (empty($originalArray)) {
            return [];
        }

        // If no new images, delete all original images
        if (empty($newArray)) {
            return $originalArray;
        }

        // Return images that exist in original but not in new
        return array_values(array_diff($originalArray, $newArray));
    }
    /**
     * Delete multiple image files from storage
     */
    private function deleteImageFiles(array $imagePaths): void {
        foreach ($imagePaths as $imagePath) {
            if ($imagePath && is_string($imagePath) && trim($imagePath) !== '') {
                $this->deleteImageFile(trim($imagePath));
            }
        }
    }
    /**
     * Delete a single image file from storage
     */
    private function deleteImageFile(string $imagePath): void {
        // Remove any leading slashes or path prefixes if needed
        $cleanPath = ltrim($imagePath, '/');

        if (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
        }
    }
}
