<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
 

    <main class="container my-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="home.php" class="text-secondary text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Mini Cake</li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <div class="row">
                <div class="col-md-5 text-center p-4">
                    <img src="mini-cake.png" alt="Mini Cake" class="img-fluid rounded border p-3">
                </div>
                
                <div class="col-md-7">
                    <h2 class="fw-bold">Mini Cake</h2>
                    <div class="text-warning mb-2 small">
                        4.8 <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <h3 class="text-maroon fw-bold mb-4">₱50.00</h3>
                    <hr>
                    <p class="text-muted mb-4 small">
                        A soft and moist cake topped with smooth whipped cream, finished with rich chocolate drizzle and chocolate chips. Perfectly sweet and creamy.
                    </p>
                    
                    <div class="d-flex align-items-center mb-4">
                        <span class="me-3 small">Quantity:</span>
                        <div class="input-group" style="width: 120px;">
                            <button class="btn btn-outline-secondary btn-sm">-</button>
                            <input type="text" class="form-control text-center form-control-sm" value="1">
                            <button class="btn btn-outline-secondary btn-sm">+</button>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-maroon px-4">Add to Cart</button>
                        <button class="btn btn-maroon px-5 text-white">Buy Now</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
            <h5 class="fw-bold mb-3">Product Ratings</h5>
            <div class="rating-summary-box p-3 mb-4 rounded-3" style="background-color: #f3ece4;">
                <h2 class="mb-0 fw-bold">4.8 <small class="fs-6 fw-normal">out of 5</small></h2>
                <div class="text-warning">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
            </div>
            
            <div class="review-item mb-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-user-circle fa-2x text-secondary me-2"></i>
                    <span class="fw-bold small">C***_*****y</span>
                </div>
                <div class="text-warning small mb-2">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="small text-muted">Akala ko mini cake lang siya... pero yung saya ko after kumain, hindi mini!</p>
                <hr>
            </div>
        </div>

        <h6 class="text-uppercase fw-bold text-secondary mb-3 small">You May Also Like</h6>
        <div class="row g-3">
            <div class="col-md-3">
                <a href="ProductInterface.php" class="text-decoration-none">
                    <div class="product-card">
                        <div class="product-image"><img src="whoopie-pie.png" alt="Whoopie Pie"></div>
                        <div class="product-details">
                            <h6 class="mb-1">Whoopie Pie</h6>
                            <div class="price">₱80.00</div>
                        </div>
                    </div>
                </a>
            </div>
            </div> 
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>