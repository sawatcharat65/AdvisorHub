<?php
session_start();

if(isset($_POST['logout'])){
  session_destroy();
  header('location: /AdvisorHub/login');
}

if (!isset($_SESSION['username']) && !isset($_SESSION['id'])) {
  die(header("location:http://localhost/AdvisorHub/login"));
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>กรอกข้อมูล สมน.1</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <nav>
    <div class="logo">
      <img src="../CSIT.png" alt="" width="250px">
    </div>
    <ul>
      <li><a href="/AdvisorHub/home">Home</a></li>

      <?php
      if (isset($_SESSION['username'])) {
        echo
          "
                    <li><a href='/AdvisorHub/advisor'>Advisor</a></li>
                    <li><a href='/AdvisorHub/inbox'>Inbox</a></li>
                    <li><a href='/AdvisorHub/thesis/thesis.php'>Thesis</a></li>
                    <li><a href='/AdvisorHub/statistics'>Statistics</a></li>
                    <li><a href='/AdvisorHub/thesis_resource_list/thesis_resource_list.php'>File</a></li>
                    ";
      } else {
        echo "<li><a href='/AdvisorHub/login'>Login</a></li>";
      }
      ?>
    </ul>

    <div class="userProfile">
      <?php
      if (isset($_SESSION['username'])) {
        echo '<h2>' . $_SESSION['username'] . '<h2/>';
        echo "<i class='bx bxs-user-circle' ></i>";
        echo "<div class='dropdown'>
                            <form action='' method='post'>
                                <button name='profile'>Profile</button>
                                <button name='logout'>Logout</button>
                            </form>
                        </div>";
      }
      ?>
    </div>
  </nav>

  <div class="container my-5">
    <form action="advisor_request.php" method="POST">
      <!-- academic year and semester -->
      <div class="mb-3 row align-items-center">

        <div class="col-auto">
          <label for="academic_year">ปีการศึกษา:</label>
          <input type="number" class="form-control" id="academic_year" name="academic_year" required
            style="width: 100px;">
        </div>

        <div class="col-auto">
          <label for="semester">ภาคเรียน:</label>
          <select id="semester" name="semester" class="form-select w-auto" required>
            <option value="1" selected>1</option>
            <option value="2">2</option>
          </select>
        </div>

      </div>



      <!-- Dropdown -->
      <div class="mb-3 d-flex align-items-center">
        <label for="thesisType">ทำวิทยานิพนธ์ประเภท:</label>
        <select id="thesisType" name="thesisType" class="form-select w-auto" onchange="toggleFields()" required>
          <option value="single" selected>เดี่ยว</option>
          <option value="pair">คู่</option>
        </select>
      </div>

      <!-- ฟิลด์ทำเดี่ยว -->
      <div id="singleFields">
        <h5>ข้อมูลนิสิต (ทำเดี่ยว)</h5>

        <div class="row align-items-center mb-3">
          <div class="col-md-7">
            <label for="singleName" class="form-label">ชื่อ-สกุล:</label>
            <input type="text" class="form-control" id="singleName" name="singleName" placeholder="ไม่ต้องระบุคำนำหน้า"
              required>
          </div>
          <div class="col-md-5">
            <label for="singleStudentID" class="form-label">รหัสนิสิต:</label>
            <input type="text" class="form-control" id="singleStudentID" name="singleStudentID" required>
          </div>
        </div>

        <div class="row align-items-center mb-3">
          <div class="col-md-4">
            <label for="singleBranch" class="form-label">สาขา:</label>
            <select id="singleBranch" class="form-select" name="singleBranch" required>
              <option value="CS">วิทยาการคอมพิวเตอร์</option>
              <option value="IT">เทคโนโลยีสารสนเทศ</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="singlePhone" class="form-label">เบอร์มือถือ:</label>
            <input type="tel" class="form-control" id="singlePhone" name="singlePhone" pattern="\d{10}"
              placeholder="08xxxxxxxx" required>
          </div>
          <div class="col-md-4">
            <label for="singleEmail" class="form-label">อีเมล:</label>
            <input type="email" class="form-control" id="singleEmail" name="singleEmail" placeholder="email@nu.ac.th"
              required>
          </div>
        </div>


      </div>

      <!-- ฟิลด์ทำคู่ -->
      <div id="pairFields" class="hidden">
        <h5>ข้อมูลนิสิต (คนที่ 1)</h5>

        <div class="row align-items-center mb-3">
          <div class="col-md-7">
            <label for="pairName1" class="form-label">ชื่อ-สกุล:</label>
            <input type="text" class="form-control" id="pairName1" name="pairName1" placeholder="ไม่ต้องระบุคำนำหน้า"
              >
          </div>
          <div class="col-md-5">
            <label for="pairStudentID1" class="form-label">รหัสนิสิต:</label>
            <input type="text" class="form-control" id="pairStudentID1" name="pairStudentID1" >
          </div>
        </div>

        <div class="row align-items-center mb-3">
          <div class="col-md-4">
            <label for="pairBranch1" class="form-label">สาขา:</label>
            <select id="pairBranch1" class="form-select" name="pairBranch1" >
              <option value="CS">วิทยาการคอมพิวเตอร์</option>
              <option value="IT">เทคโนโลยีสารสนเทศ</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="pairPhone1" class="form-label">เบอร์มือถือ:</label>
            <input type="tel" class="form-control" id="pairPhone1" name="pairPhone1" pattern="\d{10}"
              placeholder="08xxxxxxxx" >
          </div>
          <div class="col-md-4">
            <label for="pairEmail1" class="form-label">อีเมล:</label>
            <input type="email" class="form-control" id="pairEmail1" name="pairEmail1" placeholder="email@nu.ac.th"
              >
          </div>
        </div>




        <h5>ข้อมูลนิสิต (คนที่ 2)</h5>


        <div class="row align-items-center mb-3">
          <div class="col-md-7">
            <label for="pairName2" class="form-label">ชื่อ-สกุล:</label>
            <input type="text" class="form-control" id="pairName2" name="pairName2" placeholder="ไม่ต้องระบุคำนำหน้า"
              >
          </div>
          <div class="col-md-5">
            <label for="pairStudentID2" class="form-label">รหัสนิสิต:</label>
            <input type="text" class="form-control" id="pairStudentID2" name="pairStudentID2" >
          </div>
        </div>

        <div class="row align-items-center mb-3">
          <div class="col-md-4">
            <label for="pairBranch2" class="form-label">สาขา:</label>
            <select id="pairBranch2" class="form-select" name="pairBranch2" >
              <option value="CS">วิทยาการคอมพิวเตอร์</option>
              <option value="IT">เทคโนโลยีสารสนเทศ</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="pairPhone2" class="form-label">เบอร์มือถือ:</label>
            <input type="tel" class="form-control" id="pairPhone2" name="pairPhone2" pattern="\d{10}"
              placeholder="08xxxxxxxx" >
          </div>
          <div class="col-md-4">
            <label for="pairEmail2" class="form-label">อีเมล:</label>
            <input type="email" class="form-control" id="pairEmail2" name="pairEmail2" placeholder="email@nu.ac.th"
              >
          </div>
        </div>



      </div>

      <div class="mb-3">
        <label for="advisorName" class="form-label">อาจารย์ที่ปรึกษาวิทยานิพนธ์:</label>
        <input type="text" class="form-control" id="advisorName" name="advisorName" required value="ผศ.ดร. xxxxx xxxxx" readonly>
      </div>

      <!-- ข้อมูล -->
      <div class="mb-3">
        <label for="thesisTitleThai" class="form-label">ชื่อเรื่อง (ภาษาไทย):</label>
        <input type="text" class="form-control" id="thesisTitleThai" name="thesisTitleThai" required>
      </div>
      <div class="mb-3">
        <label for="thesisTitleEnglish" class="form-label">ชื่อเรื่อง (ภาษาอังกฤษ):</label>
        <input type="text" class="form-control" id="thesisTitleEnglish" name="thesisTitleEnglish" required>
      </div>
      <div class="mb-3">
        <label for="thesisDescription" class="form-label">รายละเอียดวิทยานิพนธ์โดยสังเขป:</label>
        <textarea class="form-control" id="thesisDescription" name="thesisDescription" rows="4" required></textarea>
      </div>

      <!-- submit -->
      <div class="text-start mt-4">
        <button type="submit" class="btn" style="color:white; background-color: #ff9300;">ส่งคำร้อง</button>
      </div>
    </form>
  </div>

  <script>
    function toggleFields() {
      const thesisType = document.getElementById('thesisType').value;
      const singleFields = document.getElementById('singleFields');
      const pairFields = document.getElementById('pairFields');

      if (thesisType === 'single') {
        singleFields.style.display = 'block';
        pairFields.style.display = 'none';
      } else if (thesisType === 'pair') {
        singleFields.style.display = 'none';
        pairFields.style.display = 'block';
      }
    }

    // ตั้งค่าเริ่มต้นให้แสดงฟิลด์สำหรับ ทำเดี่ยว
    window.onload = toggleFields;
  </script>
</body>

</html>