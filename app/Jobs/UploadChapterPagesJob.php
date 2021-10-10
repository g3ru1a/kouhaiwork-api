<?php

namespace App\Jobs;

use App\Http\Controllers\MediaController;
use App\Models\Chapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadChapterPagesJob extends Job
{
    public $tries = 5;
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
            $ch = Chapter::with('pages')->findOrFail($this->chapter->id);
            if ($pages) {
                if(count($ch->pages) == 0) $next_id = null;
                else $next_id = $ch->pages->first()->id;
                // Log::info($ch->pages->first()->id);
                $seriesName = substr($this->manga->title, 0, 60);
                for ($i = count($order) - 1; $i >= 0; $i--) {
                    $ind = $order[$i];
                    $page = MediaController::uploadPage($pages[$ind], $next_id, 'chapters/' . $seriesName . '/' . $this->chapter->number, $next_id !== null ? false : true);
                    $this->chapter->pages()->save($page);
                    $next_id = $page->id;
                }
                $storagePath = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix();
                Log::info($storagePath . explode('/', $pages[$order[0]])[0]);
                MediaController::deleteDir($storagePath . explode('/', $pages[$order[0]])[0]);
                $this->chapter->uploaded = true;
                $this->chapter->save();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
