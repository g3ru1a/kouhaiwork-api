<?php

namespace App\Jobs;

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Storage;

class UploadChapterPagesJob extends Job
{
    protected $pages, $order, $manga, $chapter;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pages, $order, $manga, $chapter)
    {
        $this->pages = $pages;
        $this->order = $order;
        $this->manga = $manga;
        $this->chapter = $chapter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $pages = $this->pages;
            $order = json_decode($this->order);
            if ($pages) {
                $next_id = null;
                $seriesName = substr($this->manga->title, 0, 60);
                for ($i = count($order) - 1; $i >= 0; $i--) {
                    $f = Storage::disk('public')->get($pages[$order[$i]]);
                    $page = MediaController::uploadPage($pages[$order[$i]], $next_id, 'chapters/' . $seriesName . '/' . $this->chapter->number, $next_id !== null ? false : true);
                    $this->chapter->pages()->save($page);
                    $next_id = $page->id;
                }
                $this->chapter->uploaded = true;
                $this->chapter->save();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
