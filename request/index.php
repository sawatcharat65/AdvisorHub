<?php
include('../components/navbar.php');
include('../server.php');

session_start();

if (empty($_SESSION['advisor_id'])) {
  header('location: /AdvisorHub/advisor');
}

if (isset($_POST['logout'])) {
  session_destroy();
  header('location: /AdvisorHub/login');
}

if (!isset($_SESSION['username']) && !isset($_SESSION['id'])) {
  die(header("location:http://localhost/AdvisorHub/login"));
}

$id = $_SESSION['id'];
$username = $_SESSION['username'];

// sql สำหรับเช็คว่าอยู่ใน role อะไร (advisor, student, admin)
$check_sql = "SELECT role FROM account WHERE id = '{$id}'";
$check_result = mysqli_query($conn, $check_sql);
$check_row = mysqli_fetch_array($check_result);

// condition สำหรับ sql ดึงข้อมูลนิสิต
if ($check_row['role'] == 'student') {
  $sql = "SELECT * FROM student WHERE id = '{$id}'";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_array($result);
}

// sql สำหรับเรียกข้อมูลอาจารย์
$advisor_sql = "SELECT first_name, last_name FROM advisor WHERE id = '{$_SESSION["advisor_id"]}'";
$advisor_result = mysqli_query($conn, $advisor_sql);
$advisor_row = mysqli_fetch_array($advisor_result);

?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>แบบฟอร์มส่งคำร้อง</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <?php renderNavbar(['home', 'advisor', 'inbox', 'statistics', 'Teams']) ?>

  <div class="container my-5">
    <form action="advisor_request.php" method="POST">
      <!-- academic year and semester -->
      <div class="mb-3 row align-items-center">

        <div class="col-auto">
          <label for="academic_year">ปีการศึกษา:</label>
          <input type="number" class="form-control" id="academic_year" name="academic_year"
            value="<?php echo date("Y") + 542 ?>" style="width: 100px;">
        </div>

        <div class="col-auto">
          <label for="semester">ภาคเรียน:</label>
          <select id="semester" name="semester" class="form-select w-auto">
            <option value="1" selected>1</option>
            <option value="2">2</option>
          </select>
        </div>

      </div>



      <!-- Dropdown -->
      <div class="mb-3 d-flex align-items-center">
        <label for="thesisType" class="me-2">ทำวิทยานิพนธ์ประเภท: </label>
        <select id="thesisType" name="thesisType" class="form-select w-auto" onchange="toggleFields()">
          <option value="single" selected>เดี่ยว</option>
          <option value="pair">คู่</option>
        </select>
      </div>

      <!-- ฟิลด์ทำเดี่ยว -->
      <div id="singleFields">
        <h5>ข้อมูลนิสิต (ทำเดี่ยว)</h5>

        <div class="row align-items-center mb-3">
          <div class="col-md-5">
            <label for="singleStudentID" class="form-label">รหัสนิสิต:</label>
            <input type="text" class="form-control" id="singleStudentID" name="singleStudentID"
              value="<?php echo $row['id']; ?>" readonly>
          </div>
          <div class="col-md-7">
            <label for="singleName" class="form-label">ชื่อ-สกุล:</label>
            <input type="text" class="form-control" id="singleName" name="singleName" placeholder="ไม่ต้องระบุคำนำหน้า"
              value="<?php echo $row['first_name'] . ' ' . $row['last_name']; ?>" readonly>
          </div>
        </div>

        <div class="row align-items-center mb-3">
          <div class="col-md-4">
            <label for="singleBranch" class="form-label">สาขา:</label>
            <select id="singleBranch" class="form-select" name="singleBranch">
              <option value="CS"
                <?php
                if ($row['department'] == 'Computer Science') {
                  echo 'selected';
                }
                ?>>วิทยาการคอมพิวเตอร์</option>
              <option value="IT"
                <?php
                if ($row['department'] == 'Information Technology') {
                  echo 'selected';
                }
                ?>>เทคโนโลยีสารสนเทศ</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="singlePhone" class="form-label">เบอร์มือถือ:</label>
            <input type="tel" class="form-control" id="singlePhone" name="singlePhone" placeholder="08xxxxxxxx"
              value="<?php echo $row['tel']; ?>" readonly>
          </div>
          <div class="col-md-4">
            <label for="singleEmail" class="form-label">อีเมล:</label>
            <input type="email" class="form-control" id="singleEmail" name="singleEmail" placeholder="email@nu.ac.th"
              value="<?php echo $row['email']; ?>" readonly>
          </div>
        </div>


      </div>

      <!-- ฟิลด์ทำคู่ -->
      <div id="pairFields" class="hidden">
        <h5>ข้อมูลนิสิต (คนที่ 1)</h5>

        <div class="row align-items-center mb-3">
          <div class="col-md-5">
            <label for="pairStudentID1" class="form-label">รหัสนิสิต:</label>
            <input type="text" class="form-control" id="pairStudentID1" name="pairStudentID1"
              value="<?php echo $row['id']; ?>" readonly>
          </div>
          <div class="col-md-7">
            <label for="pairName1" class="form-label">ชื่อ-สกุล:</label>
            <input type="text" class="form-control" id="pairName1" name="pairName1" placeholder="ไม่ต้องระบุคำนำหน้า"
              value="<?php echo $row['first_name'] . ' ' . $row['last_name']; ?>" readonly>
          </div>
        </div>

        <div class="row align-items-center mb-3">
          <div class="col-md-4">
            <label for="pairBranch1" class="form-label">สาขา:</label>
            <select id="pairBranch1" class="form-select" name="pairBranch1">
              <option value="CS"
                <?php
                if ($row['department'] == 'Computer Science') {
                  echo 'selected';
                }
                ?>>วิทยาการคอมพิวเตอร์</option>
              <option value="IT"
                <?php
                if ($row['department'] == 'Information Technology') {
                  echo 'selected';
                }
                ?>>เทคโนโลยีสารสนเทศ</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="pairPhone1" class="form-label">เบอร์มือถือ:</label>
            <input type="tel" class="form-control" id="pairPhone1" name="pairPhone1" placeholder="08xxxxxxxx"
              value="<?php echo $row['tel']; ?>" readonly>
          </div>
          <div class="col-md-4">
            <label for="pairEmail1" class="form-label">อีเมล:</label>
            <input type="email" class="form-control" id="pairEmail1" name="pairEmail1" placeholder="email@nu.ac.th"
              value="<?php echo $row['email']; ?>" readonly>
          </div>
        </div>

        <h5>ข้อมูลนิสิต (คนที่ 2)</h5>

        <div class="row align-items-center mb-3">
          <div class="col-md-5">
            <label for="pairStudentID2" class="form-label">รหัสนิสิต:</label>
            <input type="text" class="form-control" id="pairStudentID2" name="pairStudentID2" placeholder="กรุณากรอกรหัสนิสิต" onblur="fetchStudentData()">
          </div>
          <div class="col-md-7">
            <label for="pairName2" class="form-label">ชื่อ-สกุล:</label>
            <input type="text" class="form-control" id="pairName2" name="pairName2" readonly>
          </div>
        </div>

        <div class="row align-items-center mb-3">
          <div class="col-md-4">
            <label for="pairBranch2" class="form-label">สาขา:</label>
            <select id="pairBranch2" class="form-select" name="pairBranch2">
              <option value="CS">วิทยาการคอมพิวเตอร์</option>
              <option value="IT">เทคโนโลยีสารสนเทศ</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="pairPhone2" class="form-label">เบอร์มือถือ:</label>
            <input type="tel" class="form-control" id="pairPhone2" name="pairPhone2" readonly>
          </div>
          <div class="col-md-4">
            <label for="pairEmail2" class="form-label">อีเมล:</label>
            <input type="email" class="form-control" id="pairEmail2" name="pairEmail2" readonly>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label for="advisorName" class="form-label">อาจารย์ที่ปรึกษาวิทยานิพนธ์:</label>
        <input type="text" class="form-control" id="advisorName" name="advisorName"
          value="<?php echo $advisor_row['first_name'] . ' ' . $advisor_row['last_name']; ?>" readonly>
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

      <input type="text" hidden name="advisor_id" id="advisor_id" value="<?php echo $_SESSION['advisor_id'] ?>">

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

  <script>
    function fetchStudentData() {
      let studentID = document.getElementById('pairStudentID2').value.trim();

      if (studentID === '') {
        clearStudentFields();
        return;
      }

      fetch(`fetch_student.php?studentID=${studentID}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('pairName2').value = data.name;
            document.getElementById('pairBranch2').value = data.branch;
            document.getElementById('pairPhone2').value = data.phone;
            document.getElementById('pairEmail2').value = data.email;
          } else {
            clearStudentFields();
          }
        })
        .catch(error => {
          console.error('Error:', error);
          clearStudentFields();
        });
    }

    // ฟังก์ชันสำหรับเคลียร์ช่องข้อมูล
    function clearStudentFields() {
      document.getElementById('pairName2').value = '';
      document.getElementById('pairBranch2').value = '';
      document.getElementById('pairPhone2').value = '';
      document.getElementById('pairEmail2').value = '';
    }
  </script>
</body>

</html>