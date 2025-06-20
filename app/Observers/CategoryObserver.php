<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryObserver {
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void {
        //
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void {
        // Check if the image field was changed
        if ($category->isDirty('image')) {
            $originalImage = $category->getOriginal('image');

            // Delete the old image if it exists and is different from the new one
            if ($originalImage && $originalImage !== $category->image) {
                $this->deleteImageFile($originalImage);
            }
        }
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void {
        // Delete the image file when category is deleted
        if ($category->image) {
            $this->deleteImageFile($category->image);
        }
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void {
        //
    }

    private function deleteImageFile(string $imagePath): void {
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
