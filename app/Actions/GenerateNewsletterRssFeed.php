<?php

namespace App\Actions;

use App\Models\NewsletterIssue;

class GenerateNewsletterRssFeed
{
    public function handle(): string
    {
        $issues = NewsletterIssue::published()
            ->latest('published_at')
            ->take(20)
            ->get();

        $siteUrl = url('/newsletter');
        $feedUrl = route('newsletter.rss');
        $lastBuild = $issues->first()?->publishedAt()?->toRssString() ?? now()->toRssString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
        $xml .= "<channel>\n";
        $xml .= '<title>The Laravel Architect Newsletter</title>'."\n";
        $xml .= '<link>'.$this->escape($siteUrl)."</link>\n";
        $xml .= '<description>Practical Laravel architecture notes, tutorials, and updates from The Laravel Architect.</description>'."\n";
        $xml .= "<language>en-us</language>\n";
        $xml .= "<lastBuildDate>{$lastBuild}</lastBuildDate>\n";
        $xml .= '<atom:link href="'.$this->escape($feedUrl).'" rel="self" type="application/rss+xml" />'."\n";

        foreach ($issues as $issue) {
            $link = route('newsletter.issue', $issue);

            $xml .= "<item>\n";
            $xml .= '<title>'.$this->escape($issue->title)."</title>\n";
            $xml .= '<link>'.$this->escape($link)."</link>\n";
            $xml .= '<guid isPermaLink="true">'.$this->escape($link)."</guid>\n";
            $xml .= '<description>'.$this->escape($issue->excerpt ?? '')."</description>\n";
            $xml .= '<pubDate>'.($issue->publishedAt()?->toRssString() ?? now()->toRssString())."</pubDate>\n";
            $xml .= "</item>\n";
        }

        return $xml."</channel>\n</rss>";
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }
}
