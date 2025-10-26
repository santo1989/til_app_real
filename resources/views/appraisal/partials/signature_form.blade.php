@php
    // Signature form supports typed-name or drawn signature via canvas. It posts 'role', 'name' and optional 'image' (base64)
    $formId = 'sig_form_' . ($role ?? 'r') . '_' . ($appraisal->id ?? '0');
    $canvasId = 'sig_canvas_' . ($role ?? 'r') . '_' . ($appraisal->id ?? '0');
    $inputImage = 'sig_image_' . ($role ?? 'r') . '_' . ($appraisal->id ?? '0');
@endphp

<form id="{{ $formId }}" action="{{ route('appraisals.sign', ['appraisal_id' => $appraisal->id]) }}" method="POST"
    class="d-inline-block">
    @csrf
    <input type="hidden" name="role" value="{{ $role }}">
    <input type="hidden" id="{{ $inputImage }}" name="image">
    <div class="form-inline align-items-center">
        <input type="text" name="name" class="form-control form-control-sm mr-2" placeholder="Your name">
        <button type="button" class="btn btn-sm btn-secondary mr-2"
            onclick="toggleCanvas('{{ $canvasId }}')">Draw</button>
        <button class="btn btn-sm btn-primary mr-2" type="submit">Sign (Name)</button>
        <button type="button" class="btn btn-sm btn-success"
            onclick="submitCanvasSignature('{{ $formId }}','{{ $canvasId }}','{{ $inputImage }}')">Sign
            (Drawn)</button>
    </div>
    <div style="display:none; margin-top:8px;" id="wrap_{{ $canvasId }}">
        <div style="display:flex; gap:12px; align-items:flex-start;">
            <div>
                <canvas id="{{ $canvasId }}" width="300" height="100"
                    style="border:1px solid #ccc; background:#fff; touch-action:none;">Your browser does not support
                    canvas</canvas>
                <div class="mt-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="clearCanvas('{{ $canvasId }}')">Clear</button>
                </div>
            </div>
            <div style="min-width:220px;">
                <div style="font-weight:600; margin-bottom:6px">Preview</div>
                <div style="border:1px solid #eee; padding:6px; background:#fff;">
                    <img id="preview_{{ $canvasId }}" alt="Signature preview"
                        style="max-width:220px; height:auto; display:block;" />
                </div>
                <div style="margin-top:6px; font-size:12px; color:#666;">
                    <div id="sizeinfo_{{ $canvasId }}">No signature drawn</div>
                    <div id="warn_{{ $canvasId }}" style="color:#b71c1c; display:none;">Large signature will be
                        resized before upload.</div>
                </div>
            </div>
        </div>
    </div>
</form>

@once
    @push('scripts')
        <script>
            function toggleCanvas(id) {
                const el = document.getElementById('wrap_' + id);
                if (!el) return;
                el.style.display = el.style.display === 'none' || el.style.display === '' ? 'block' : 'none';
                initCanvasOnce(id);
            }

            const _sigInited = {};

            function initCanvasOnce(id) {
                if (_sigInited[id]) return;
                _sigInited[id] = true;
                const canvas = document.getElementById(id);
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2;
                let drawing = false;
                let lastX = 0,
                    lastY = 0;

                function getPos(e) {
                    if (e.touches && e.touches.length) {
                        const rect = canvas.getBoundingClientRect();
                        return {
                            x: e.touches[0].clientX - rect.left,
                            y: e.touches[0].clientY - rect.top
                        };
                    }
                    const rect = canvas.getBoundingClientRect();
                    return {
                        x: e.clientX - rect.left,
                        y: e.clientY - rect.top
                    };
                }

                canvas.addEventListener('pointerdown', (e) => {
                    drawing = true;
                    const p = getPos(e);
                    lastX = p.x;
                    lastY = p.y;
                });
                canvas.addEventListener('pointermove', (e) => {
                    if (!drawing) return;
                    const p = getPos(e);
                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(p.x, p.y);
                    ctx.stroke();
                    lastX = p.x;
                    lastY = p.y;
                });
                canvas.addEventListener('pointerup', () => drawing = false);
                canvas.addEventListener('pointerleave', () => drawing = false);

                // attach a small preview updater
                function updatePreview() {
                    try {
                        const preview = document.getElementById('preview_' + id);
                        const sizeEl = document.getElementById('sizeinfo_' + id);
                        const warnEl = document.getElementById('warn_' + id);
                        if (!preview) return;
                        // create a small canvas for preview to control size
                        const tmp = document.createElement('canvas');
                        const scale = Math.min(1, 220 / canvas.width);
                        tmp.width = Math.round(canvas.width * scale);
                        tmp.height = Math.round(canvas.height * scale);
                        const tctx = tmp.getContext('2d');
                        tctx.fillStyle = '#ffffff';
                        tctx.fillRect(0, 0, tmp.width, tmp.height);
                        tctx.drawImage(canvas, 0, 0, tmp.width, tmp.height);
                        const dataUrl = tmp.toDataURL('image/png');
                        preview.src = dataUrl;
                        // estimate size of preview
                        const head = 'data:image/png;base64,';
                        const base64Length = dataUrl.length - head.length;
                        const sizeInBytes = Math.round(base64Length * 3 / 4);
                        const kb = Math.round(sizeInBytes / 1024);
                        if (sizeEl) sizeEl.innerText = kb + ' KB (preview)';
                        if (warnEl) warnEl.style.display = kb > 200 ? 'block' : 'none';
                    } catch (e) {
                        /* ignore preview failures */ }
                }

                canvas.addEventListener('pointerup', updatePreview);
                canvas.addEventListener('pointerleave', updatePreview);
                canvas.addEventListener('mouseup', updatePreview);
                canvas.addEventListener('mouseleave', updatePreview);

                // support mouse events fallback
                canvas.addEventListener('mousedown', (e) => {
                    drawing = true;
                    const p = getPos(e);
                    lastX = p.x;
                    lastY = p.y;
                });
                canvas.addEventListener('mousemove', (e) => {
                    if (!drawing) return;
                    const p = getPos(e);
                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(p.x, p.y);
                    ctx.stroke();
                    lastX = p.x;
                    lastY = p.y;
                });
                canvas.addEventListener('mouseup', () => drawing = false);
                canvas.addEventListener('mouseleave', () => drawing = false);
            }

            function clearCanvas(id) {
                const canvas = document.getElementById(id);
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }

            function submitCanvasSignature(formId, canvasId, inputId) {
                const canvas = document.getElementById(canvasId);
                if (!canvas) {
                    alert('Canvas not initialized');
                    return;
                }
                const dataUrl = canvas.toDataURL('image/png');
                document.getElementById(inputId).value = dataUrl;
                // submit the form
                document.getElementById(formId).submit();
            }
        </script>
    @endpush
@endonce
