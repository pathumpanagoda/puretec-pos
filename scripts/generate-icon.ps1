# Script to convert the brand logo PNG to Windows ICO format for desktop icons.

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$rootDir = Split-Path -Parent $scriptDir

$logoPath = Join-Path $rootDir "public\icons\logo png.png"
$outputPath = Join-Path $rootDir "electron\build\icon.ico"

Write-Host "Converting logo to Windows ICO format..." -ForegroundColor Cyan
Write-Host "Source: $logoPath" -ForegroundColor Gray
Write-Host "Target: $outputPath" -ForegroundColor Gray

if (-not (Test-Path $logoPath)) {
    Write-Host "[ERROR] Logo file not found at: $logoPath" -ForegroundColor Red
    exit 1
}

$csharpSource = @"
using System;
using System.Drawing;
using System.IO;

public class PngIconConverter {
    public static void Convert(string inputPath, string outputPath, int size) {
        using (var bitmap = new Bitmap(inputPath))
        using (var resized = new Bitmap(bitmap, new Size(size, size)))
        using (var stream = new FileStream(outputPath, FileMode.Create)) {
            // Write ICO Header
            var writer = new BinaryWriter(stream);
            writer.Write((short)0); // Reserved
            writer.Write((short)1); // Type: 1 = Icon
            writer.Write((short)1); // Count: 1 image
            
            // Write Image Entry
            // 256 is represented by 0 byte value in the ICO header format
            byte sizeByte = (byte)(size >= 256 ? 0 : size);
            writer.Write(sizeByte); // Width
            writer.Write(sizeByte); // Height
            writer.Write((byte)0); 
            writer.Write((byte)0); 
            writer.Write((short)0); 
            writer.Write((short)32); // Bits per pixel
            
            var ms = new MemoryStream();
            resized.Save(ms, System.Drawing.Imaging.ImageFormat.Png);
            writer.Write((int)ms.Length);
            writer.Write((int)22); // Offset
            writer.Write(ms.ToArray());
            writer.Flush();
        }
    }
}
"@

Add-Type -TypeDefinition $csharpSource -ReferencedAssemblies System.Drawing
[PngIconConverter]::Convert($logoPath, $outputPath, 256)

if (Test-Path $outputPath) {
    Write-Host "✅ Successfully generated: $outputPath" -ForegroundColor Green
} else {
    Write-Host "❌ Failed to generate icon." -ForegroundColor Red
    exit 1
}
