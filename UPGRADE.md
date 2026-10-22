# Upgrade Guide

## 0.2.0

- Replace `ImageCropper` calls with positive named `maxPixels`, `maxDimension`, and `maxInputBytes` limits, and move
  positional quality arguments to `quality`. Configure these limits from the upload worker's measured memory allowance.
- Clear previously published FilePond asset directories once so they include both readable and minified variants.
