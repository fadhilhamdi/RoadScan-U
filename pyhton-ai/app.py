import os
import cv2
import numpy as np
import base64
from flask import Flask, request, jsonify
from flask_cors import CORS
import tensorflow as tf

app = Flask(__name__)
CORS(app)

# =========================
# LOAD BEST MODEL ONLY
# =========================
MODEL = None

def load_best_model():

    global MODEL

    if MODEL is None:

        model_path = os.path.join(
            'models',
            'model_training10_tf'
        )

        if not os.path.exists(model_path):
            raise Exception(f"Model tidak ditemukan: {model_path}")

        print("🔄 Loading Best Model...")

        model = tf.saved_model.load(model_path)

        # ambil serving function
        MODEL = model.signatures["serving_default"]

        print("✅ Best Model loaded")

    return MODEL


# =========================
# PREPROCESS (GRAYSCALE)
# =========================
def preprocess_image(image_bytes, target_size=(256, 256)):

    nparr = np.frombuffer(image_bytes, np.uint8)

    img = cv2.imdecode(nparr, cv2.IMREAD_GRAYSCALE)

    if img is None:
        return None, None

    img_resized = cv2.resize(img, target_size)

    # (H, W, 1)
    img_resized = np.expand_dims(img_resized, axis=-1)

    img_normalized = img_resized / 255.0

    return np.expand_dims(img_normalized, axis=0), img_resized


# =========================
# METRICS
# =========================
def calculate_metrics(y_true, y_pred):

    y_true = (y_true > 127).astype(np.uint8).flatten()
    y_pred = (y_pred > 127).astype(np.uint8).flatten()

    intersection = np.logical_and(y_true, y_pred).sum()
    union = np.logical_or(y_true, y_pred).sum()

    iou = intersection / union if union > 0 else 1.0

    dice = (
        (2. * intersection) /
        (y_true.sum() + y_pred.sum())
        if (y_true.sum() + y_pred.sum()) > 0
        else 1.0
    )

    accuracy = np.mean(y_true == y_pred) * 100

    rmse = np.sqrt(np.mean((y_true - y_pred) ** 2))

    return (
        round(accuracy, 2),
        round(iou, 4),
        round(dice, 4),
        round(rmse, 4)
    )


# =========================
# ROUTE PREDICT
# =========================
@app.route('/predict', methods=['POST'])
def predict():

    try:

        # =========================
        # VALIDASI FILE
        # =========================
        if 'image' not in request.files:
            return jsonify({
                'error': 'File image tidak ditemukan'
            }), 400

        file = request.files['image']

        gt_file = request.files.get('gt_image')

        # =========================
        # LOAD MODEL
        # =========================
        infer = load_best_model()

        # =========================
        # PREPROCESS
        # =========================
        input_tensor, original_gray = preprocess_image(
            file.read()
        )

        if input_tensor is None:
            return jsonify({
                'error': 'Gagal membaca gambar'
            }), 400

        # =========================
        # PREDICT
        # =========================
        input_tensor = tf.constant(
            input_tensor.astype(np.float32)
        )

        output = infer(input_tensor)

        # ambil output pertama
        prediction = list(output.values())[0].numpy()[0]

        prediction = np.squeeze(prediction)

        # threshold
        mask = (prediction > 0.5).astype(np.uint8) * 255

        if len(mask.shape) == 3:
            mask = np.squeeze(mask, axis=-1)

        # =========================
        # METRICS
        # =========================
        res_metrics = {
            'akurasi': '-',
            'iou': '-',
            'dice': '-',
            'rmse': '-'
        }

        if gt_file:

            _, gt_gray = preprocess_image(
                gt_file.read()
            )

            acc, iou, dice, rmse = calculate_metrics(
                gt_gray,
                mask
            )

            res_metrics = {
                'akurasi': acc,
                'iou': iou,
                'dice': dice,
                'rmse': rmse
            }

        # =========================
        # VISUALISASI
        # =========================
        vis_img = cv2.cvtColor(
            original_gray,
            cv2.COLOR_GRAY2BGR
        )

        # warna merah area retak
        vis_img[mask == 255] = [255, 0, 0]

        # =========================
        # PERSENTASE KERUSAKAN
        # =========================
        percentage = (
            np.sum(mask == 255) / mask.size
        ) * 100

        # =========================
        # STATUS
        # =========================
        status = (
            "Low" if percentage < 10 else
            "Medium" if percentage < 25 else
            "High"
        )

        # =========================
        # ENCODE BASE64
        # =========================
        _, buf_mask = cv2.imencode('.png', mask)

        _, buf_vis = cv2.imencode('.png', vis_img)

        # =========================
        # RESPONSE
        # =========================
        return jsonify({

            'mask_base64':
                base64.b64encode(buf_mask)
                .decode('utf-8'),

            'vis_base64':
                base64.b64encode(buf_vis)
                .decode('utf-8'),

            'luas_retakan':
                round(percentage, 2),

            'status':
                status,

            **res_metrics

        })

    except Exception as e:

        print("💥 ERROR:", str(e))

        return jsonify({
            'error': str(e)
        }), 500


# =========================
# TEST ROUTE
# =========================
@app.route('/')
def home():

    return "🚀 Flask AI (Best Model) is running!"


# =========================
# MAIN
# =========================
if __name__ == '__main__':

    app.run(
        host='127.0.0.1',
        port=5000,
        debug=True
    )