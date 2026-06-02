<?php

class Player
{
    private string $ytDlp;
    private string $ffmpeg;
    private string $downloadDir;
    private string $logFile;

    public function __construct()
    {
        $binDir = __DIR__ . '/../../bin';

        $isWindows  = PHP_OS_FAMILY === 'Windows';

        $this->ytDlp  = $binDir . '/yt-dlp' . ($isWindows ? '.exe' : '');
        $this->ffmpeg = $binDir . '/ffmpeg' . ($isWindows ? '.exe' : '');

        $this->downloadDir = __DIR__ . '/../../public/downloads';
        $this->logFile     = $this->downloadDir . '/debug.log';

        if (!is_dir($this->downloadDir)) {
            mkdir($this->downloadDir, 0755, true);
        }

        foreach ([$this->ytDlp, $this->ffmpeg] as $bin) {
            if (file_exists($bin) && !is_executable($bin)) {
                chmod($bin, 0755);
            }
        }
    }

    public function getStreamUrl(string $title, string $artist): ?string
    {
        $query = $artist . ' ' . $title;
        $bin   = escapeshellarg(realpath($this->ytDlp) ?: $this->ytDlp);

        $cmd = $bin
            . ' -f bestaudio/best'
            . ' -g'
            . ' --no-playlist'
            . ' --no-check-certificate'
            . ' ' . escapeshellarg('ytsearch1:' . $query)
            . ' 2>>' . escapeshellarg($this->logFile);

        $this->log("=== preview: $query ===");
        $this->log("CMD: $cmd");

        $url = trim((string) shell_exec($cmd));

        $this->log("URL: $url");

        return ($url !== '' && str_starts_with($url, 'http')) ? $url : null;
    }

    public function download(int $musicId, string $title, string $artist): ?string
    {
        $filename  = 'music_' . $musicId . '.mp3';
        $outPath   = $this->downloadDir . '/' . $filename;
        $publicUrl = 'downloads/' . $filename;

        if (file_exists($outPath)) {
            return $publicUrl;
        }

        $query      = $artist . ' ' . $title;
        $bin        = escapeshellarg(realpath($this->ytDlp)  ?: $this->ytDlp);
        $ffmpegDir  = escapeshellarg(realpath(dirname($this->ffmpeg)) ?: dirname($this->ffmpeg));

        $cmd = 'HOME=/tmp ' . $bin
            . ' -f "bestaudio/best"'
            . ' --no-playlist'
            . ' --no-check-certificate'
            . ' --ffmpeg-location ' . $ffmpegDir
            . ' -x --audio-format mp3 --audio-quality 192K'
            . ' -o ' . escapeshellarg($outPath)
            . ' ' . escapeshellarg('ytsearch1:' . $query)
            . ' >> ' . escapeshellarg($this->logFile) . ' 2>&1';

        $this->log("=== download: $query ===");
        $this->log("CMD: $cmd");

        shell_exec($cmd);

        $ok = file_exists($outPath);
        $this->log("FILE EXISTS: " . ($ok ? 'yes' : 'no'));

        return $ok ? $publicUrl : null;
    }

    public function getLocalUrl(int $musicId): ?string
    {
        $filename = 'music_' . $musicId . '.mp3';
        $fullPath = $this->downloadDir . '/' . $filename;
        return file_exists($fullPath) ? 'downloads/' . $filename : null;
    }

    public function getLog(): string
    {
        return file_exists($this->logFile) ? file_get_contents($this->logFile) : '';
    }

    private function log(string $msg): void
    {
        file_put_contents(
            $this->logFile,
            '[' . date('H:i:s') . '] ' . $msg . PHP_EOL,
            FILE_APPEND
        );
    }
}
