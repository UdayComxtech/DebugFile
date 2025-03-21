<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>3-Color GLB Model Debug</title>
  <!-- Three.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
  <!-- GLTFLoader -->
  <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/loaders/GLTFLoader.js"></script>
  <!-- OrbitControls -->
  <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/controls/OrbitControls.js"></script>
  <style>
    body { margin: 0; padding: 0; }
    #color-controls {
      position: absolute;
      top: 10px;
      left: 10px;
      background: rgba(255,255,255,0.8);
      padding: 10px;
      border-radius: 4px;
      font-family: sans-serif;
      z-index: 10;
    }
    #color-controls label {
      display: inline-block;
      margin: 5px 0;
    }
    #color-controls input[type="color"] {
      margin-left: 5px;
    }
  </style>
</head>
<body>
  <div id="color-controls">
    <label>
      Sky Blue:
      <input type="color" id="colorSkyBlue" value="#87ceeb">
    </label>
    <br>
    <label>
      Dark Blue:
      <input type="color" id="colorDarkBlue" value="#00008b">
    </label>
    <br>
    <label>
      White:
      <input type="color" id="colorWhite" value="#ffffff">
    </label>
  </div>

  <script>
    // 1. Scene, Camera, Renderer
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0xffffff);

    const camera = new THREE.PerspectiveCamera(
      75, window.innerWidth / window.innerHeight, 0.1, 1000
    );
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.body.appendChild(renderer.domElement);

    // 2. OrbitControls
    const controls = new THREE.OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;

    // 3. Lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
    scene.add(ambientLight);
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.7);
    directionalLight.position.set(10, 10, 10);
    scene.add(directionalLight);

    // 4. Material Variables
    let skyBlueMat = null;
    let darkBlueMat = null;
    let whiteMat = null;
    const targetMaterials = [];

    // 5. Load GLB file
    const loader = new THREE.GLTFLoader();
    loader.load(
      './blankTshirt.glb',
      function(gltf) {
        const model = gltf.scene;
        scene.add(model);

        // Center & scale the model
        const box = new THREE.Box3().setFromObject(model);
        const center = box.getCenter(new THREE.Vector3());
        model.position.sub(center);
        const size = box.getSize(new THREE.Vector3());
        const maxDim = Math.max(size.x, size.y, size.z);
        const scaleFactor = 2 / maxDim; // Adjust as needed
        model.scale.set(scaleFactor, scaleFactor, scaleFactor);

        camera.position.set(0, 0, 5);
        controls.update();

        // Traverse the model and log material names
        model.traverse((node) => {
          if (node.isMesh && node.material) {
            if (Array.isArray(node.material)) {
              node.material.forEach((mat) => {
                console.log("Material found:", mat.name);
                // Attempt to match by name
                if (mat.name === 'SkyBlueMaterial') skyBlueMat = mat;
                else if (mat.name === 'DarkBlueMaterial') darkBlueMat = mat;
                else if (mat.name === 'WhiteMaterial') whiteMat = mat;
                targetMaterials.push(mat);
              });
            } else {
              console.log("Material found:", node.material.name);
              const mat = node.material;
              console.log("Material found:", mat.name);

              if (mat.name === 'SkyBlueMaterial') skyBlueMat = mat;
              else if (mat.name === 'DarkBlueMaterial') darkBlueMat = mat;
              else if (mat.name === 'WhiteMaterial') whiteMat = mat;
              targetMaterials.push(mat);
            }
          }
        });

        console.log("Target Materials Array:", targetMaterials);
        console.log("SkyBlueMaterial:", skyBlueMat);
        console.log("DarkBlueMaterial:", darkBlueMat);
        console.log("WhiteMaterial:", whiteMat);

        // Fallback: if a material wasn't found by name, use the first three materials
        if (!skyBlueMat && targetMaterials.length > 0) {
          skyBlueMat = targetMaterials[0];
        }
        if (!darkBlueMat && targetMaterials.length > 1) {
          darkBlueMat = targetMaterials[1];
        }
        if (!whiteMat && targetMaterials.length > 2) {
          whiteMat = targetMaterials[2];
        }
      },
      undefined,
      function(error) {
        console.error("Error loading GLB:", error);
      }
    );

    // 6. Animation loop
    function animate() {
      requestAnimationFrame(animate);
      controls.update();
      renderer.render(scene, camera);
    }
    animate();

    // 7. Color picker event listeners
    document.getElementById('colorSkyBlue').addEventListener('input', (e) => {
      if (skyBlueMat) {
        skyBlueMat.color.set(e.target.value);
      } else {
        console.warn("Sky Blue material not found.");
      }
    });
    document.getElementById('colorDarkBlue').addEventListener('input', (e) => {
      if (darkBlueMat) {
        darkBlueMat.color.set(e.target.value);
      } else {
        console.warn("Dark Blue material not found.");
      }
    });
    document.getElementById('colorWhite').addEventListener('input', (e) => {
      if (whiteMat) {
        whiteMat.color.set(e.target.value);
      } else {
        console.warn("White material not found.");
      }
    });

    // 8. Handle window resizing
    window.addEventListener('resize', () => {
      camera.aspect = window.innerWidth / window.innerHeight;
      camera.updateProjectionMatrix();
      renderer.setSize(window.innerWidth, window.innerHeight);
    });
  </script>
</body>
</html>