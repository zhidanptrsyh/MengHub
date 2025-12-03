return function(Window, Tabs)
    local Players = game:GetService("Players")
    local RunService = game:GetService("RunService")
    local ReplicatedStorage = game:GetService("ReplicatedStorage")
    local HttpService = game:GetService("HttpService")
    local VirtualUser = game:GetService("VirtualUser")
    local LocalPlayer = Players.LocalPlayer
    local Controllers = ReplicatedStorage:WaitForChild("Controllers")
    local Camera = workspace.CurrentCamera
    local PG = game:GetService("Players").LocalPlayer.PlayerGui
    local VIM = game:GetService("VirtualInputManager")
    local TeleportService = game:GetService("TeleportService")
    local UserInputService = game:GetService("UserInputService")
    local Tween = game:GetService("TweenService")
    local ItemsFolder = ReplicatedStorage:WaitForChild("Items")

    Tabs.PlayerTab:Section(
    {
        Title = "Movement"
    }
    )

    local WalkspeedSlider =
        Tabs.PlayerTab:Slider(
        {
            Title = "WalkSpeed",
            Desc = "",
            Step = 1,
            Value = {
                Min = 16,
                Max = 200,
                Default = 16
            },
            Callback = function(value)
                LocalPlayer.Character.Humanoid.WalkSpeed = value
            end
        }
    )

    local JumpPowerSlider =
        Tabs.PlayerTab:Slider(
        {
            Title = "JumpPower",
            Desc = "",
            -- To make float number supported,
            -- make the Step a float number.
            -- example: Step = 0.1
            Step = 1,
            Value = {
                Min = 50,
                Max = 500,
                Default = 50
            },
            Callback = function(value)
                LocalPlayer.Character.Humanoid.JumpPower = value
            end
        }
    )

    Tabs.PlayerTab:Button(
        {
            Title = "Reset Speed And Jump",
            Callback = function()
                LocalPlayer.Character.Humanoid.WalkSpeed = 16
                LocalPlayer.Character.Humanoid.JumpPower = 50
                WalkspeedSlider:Set(16)
                JumpPowerSlider:Set(50)
                WindUI:Notify(
                    {
                        Title = "Success",
                        Content = "Berhasil mereset jump and speed",
                        Duration = 2.5,
                        Icon = "laptop-minimal-check"
                    }
                )
            end
        }
    )
    Tabs.PlayerTab:Divider()

    local FLYING, QEfly, iyflyspeed = false, true, 1
    local flyKeyDown, flyKeyUp

    local function getRoot(char)
        return char:FindFirstChild("HumanoidRootPart") or char:FindFirstChild("Torso") or char:FindFirstChild("UpperTorso")
    end

    local function NOFLY()
        FLYING = false
        local char = LocalPlayer.Character
        if char then
            local hum = char:FindFirstChildOfClass("Humanoid")
            if hum then
                hum.PlatformStand = false
            end
            local root = char:FindFirstChild("HumanoidRootPart")
            if root then
                for _, v in pairs(root:GetChildren()) do
                    if v:IsA("BodyGyro") or v:IsA("BodyVelocity") then
                        v:Destroy()
                    end
                end
            end
        end
    end

    local function sFLY(vfly)
        local plr = LocalPlayer
        local char = plr.Character or plr.CharacterAdded:Wait()
        local humanoid = char:FindFirstChildOfClass("Humanoid") or char:WaitForChild("Humanoid")
        local T = getRoot(char)
        local CONTROL = {F = 0, B = 0, L = 0, R = 0, Q = 0, E = 0}
        local lCONTROL = {F = 0, B = 0, L = 0, R = 0, Q = 0, E = 0}
        local SPEED = 0

        if flyKeyDown then
            flyKeyDown:Disconnect()
        end
        if flyKeyUp then
            flyKeyUp:Disconnect()
        end

        local function FLY()
            FLYING = true
            local BG = Instance.new("BodyGyro")
            local BV = Instance.new("BodyVelocity")
            BG.P, BG.MaxTorque = 9e4, Vector3.new(9e9, 9e9, 9e9)
            BG.CFrame, BG.Parent, BV.Parent = T.CFrame, T, T
            BV.MaxForce = Vector3.new(9e9, 9e9, 9e9)
            task.spawn(
                function()
                    repeat
                        task.wait()
                        local cam = workspace.CurrentCamera
                        if not vfly then
                            humanoid.PlatformStand = true
                        end
                        SPEED =
                            (CONTROL.L + CONTROL.R ~= 0 or CONTROL.F + CONTROL.B ~= 0 or CONTROL.Q + CONTROL.E ~= 0) and
                            (iyflyspeed * 50) or
                            0
                        if SPEED ~= 0 then
                            BV.Velocity =
                                ((cam.CFrame.LookVector * (CONTROL.F + CONTROL.B)) +
                                ((cam.CFrame *
                                    CFrame.new(
                                        CONTROL.L + CONTROL.R,
                                        (CONTROL.F + CONTROL.B + CONTROL.Q + CONTROL.E) * 0.2,
                                        0
                                    ).p) -
                                    cam.CFrame.p)) *
                                SPEED
                            lCONTROL = {F = CONTROL.F, B = CONTROL.B, L = CONTROL.L, R = CONTROL.R}
                        else
                            BV.Velocity = Vector3.zero
                        end
                        BG.CFrame = cam.CFrame
                    until not FLYING
                    BG:Destroy()
                    BV:Destroy()
                    humanoid.PlatformStand = false
                end
            )
        end

        flyKeyDown =
            UserInputService.InputBegan:Connect(
            function(input)
                local k = input.KeyCode
                if k == Enum.KeyCode.W then
                    CONTROL.F = 1
                elseif k == Enum.KeyCode.S then
                    CONTROL.B = -1
                elseif k == Enum.KeyCode.A then
                    CONTROL.L = -1
                elseif k == Enum.KeyCode.D then
                    CONTROL.R = 1
                elseif k == Enum.KeyCode.E and QEfly then
                    CONTROL.Q = 1
                elseif k == Enum.KeyCode.Q and QEfly then
                    CONTROL.E = -1
                end
            end
        )

        flyKeyUp =
            UserInputService.InputEnded:Connect(
            function(input)
                local k = input.KeyCode
                if k == Enum.KeyCode.W then
                    CONTROL.F = 0
                elseif k == Enum.KeyCode.S then
                    CONTROL.B = 0
                elseif k == Enum.KeyCode.A then
                    CONTROL.L = 0
                elseif k == Enum.KeyCode.D then
                    CONTROL.R = 0
                elseif k == Enum.KeyCode.E then
                    CONTROL.Q = 0
                elseif k == Enum.KeyCode.Q then
                    CONTROL.E = 0
                end
            end
        )

        FLY()
    end

    local function mobilefly(plr)
        FLYING = true
        local char = plr.Character or plr.CharacterAdded:Wait()
        local root = char:WaitForChild("HumanoidRootPart")
        local humanoid = char:FindFirstChildOfClass("Humanoid")
        local cam = workspace.CurrentCamera
        local control =
            require(plr:WaitForChild("PlayerScripts"):WaitForChild("PlayerModule"):WaitForChild("ControlModule"))

        local bv, bg = Instance.new("BodyVelocity"), Instance.new("BodyGyro")
        bv.MaxForce = Vector3.new(9e9, 9e9, 9e9)
        bg.MaxTorque, bg.P, bg.D = Vector3.new(9e9, 9e9, 9e9), 1000, 50
        bv.Parent, bg.Parent = root, root
        humanoid.PlatformStand = true

        task.spawn(
            function()
                while FLYING and task.wait() do
                    local move = control:GetMoveVector()
                    bg.CFrame = cam.CFrame
                    local dir = (cam.CFrame.RightVector * move.X + cam.CFrame.LookVector * -move.Z)
                    bv.Velocity = dir * (iyflyspeed * 50)
                end
                humanoid.PlatformStand = false
                bv:Destroy()
                bg:Destroy()
            end
        )
    end

    local FlySlider =
        Tabs.PlayerTab:Slider(
        {
            Title = "Fly Speed",
            Step = 1,
            Value = {
                Min = 1,
                Max = 10,
                Default = 1
            },
            Callback = function(value)
                iyflyspeed = value
            end
        }
    )

    Tabs.PlayerTab:Toggle(
        {
            Title = "Enable Fly",
            Value = false,
            Callback = function(state)
                if state then
                    if UserInputService.TouchEnabled then
                        mobilefly(LocalPlayer)
                    else
                        sFLY()
                    end
                else
                    NOFLY()
                end
            end
        }
    )

    Tabs.PlayerTab:Section(
        {
            Title = "Modes"
        }
    )

    local NoAnimationToggle =
        Tabs.PlayerTab:Toggle(
        {
            Title = "No Animations",
            Value = false,
            Callback = function(state)
                local char = LocalPlayer.Character or LocalPlayer.CharacterAdded:Wait()
                local humanoid = char:WaitForChild("Humanoid")
                local animator = humanoid:FindFirstChildOfClass("Animator")
                if not animator then
                    return
                end

                if state then
                    NoAnimationEnable = true
                    -- Stop semua animasi yang sedang main
                    for _, track in ipairs(animator:GetPlayingAnimationTracks()) do
                        track:Stop(0)
                    end
                    -- Hook untuk stop animasi baru
                    NoAnimConn =
                        animator.AnimationPlayed:Connect(
                        function(track)
                            if NoAnimationEnable then
                                task.defer(
                                    function()
                                        pcall(
                                            function()
                                                track:Stop(0)
                                            end
                                        )
                                    end
                                )
                            end
                        end
                    )
                else
                    NoAnimationEnable = false
                    if NoAnimConn then
                        NoAnimConn:Disconnect()
                        NoAnimConn = nil
                    end
                end
            end
        }
    )
    mengConfig:Register("noAnimationToggle", NoAnimationToggle)

    Tabs.PlayerTab:Toggle(
        {
            Title = "Hide Rod On Hand",
            Desc = "This feature irivisible! and hide other player too",
            Value = false,
            Callback = function(state)
                IrRod = state
                if state then
                    task.spawn(
                        function()
                            while IrRod do
                                for _, v1049 in ipairs(workspace.Characters:GetChildren()) do
                                    local l_v1049_FirstChild_0 = v1049:FindFirstChild("!!!EQUIPPED_TOOL!!!")
                                    if l_v1049_FirstChild_0 then
                                        l_v1049_FirstChild_0:Destroy()
                                    end
                                end
                                task.wait(1)
                            end
                        end
                    )
                end
            end
        }
    )
    Tabs.PlayerTab:Divider()

    Tabs.PlayerTab:Toggle(
        {
            Title = "Infinite Jump",
            Value = false,
            Callback = function(state)
                InfiniteJumpEnabled = state
            end
        }
    )

    UserInputService.JumpRequest:Connect(
        function()
            if InfiniteJumpEnabled and LocalPlayer.Character and LocalPlayer.Character:FindFirstChild("Humanoid") then
                LocalPlayer.Character.Humanoid:ChangeState(Enum.HumanoidStateType.Jumping)
            end
        end
    )

    Tabs.PlayerTab:Toggle(
        {
            Title = "Noclip",
            Value = false,
            Callback = function(state)
                NoclipEnabled = state

                if NoclipEnabled then
                    WindUI:Notify(
                        {
                            Title = "Success",
                            Content = "Anda mengaktifkan Noclip",
                            Duration = 2.5,
                            Icon = "laptop-minimal-check"
                        }
                    )
                end

                -- Saat dimatikan, kembalikan CanCollide
                local character = LocalPlayer.Character
                if character then
                    for _, part in pairs(character:GetDescendants()) do
                        if part:IsA("BasePart") then
                            part.CanCollide = true
                        end
                    end
                end
            end
        }
    )

    -- Loop Noclip
    RunService.Stepped:Connect(
        function()
            if NoclipEnabled and LocalPlayer.Character then
                for _, v in pairs(LocalPlayer.Character:GetDescendants()) do
                    if v:IsA("BasePart") and v.CanCollide then
                        v.CanCollide = false
                    end
                end
            end
        end
    )

    local v228 = false
    local v229 = nil
    local v230 = nil
    local v231 = -1.8
    local l_v228_0, l_v229_0, l_v230_0, l_v231_0 = v228, v229, v230, v231
    local walkOnWaterToggle =
        Tabs.PlayerTab:Toggle(
        {
            Title = "Walk On Water",
            Value = false,
            Callback = function(state)
                l_v228_0 = state
                local l_HumanoidRootPart_2 =
                    (LocalPlayer.Character or LocalPlayer.CharacterAdded:Wait()):WaitForChild("HumanoidRootPart")
                if state then
                    l_v229_0 = Instance.new("Part")
                    l_v229_0.Name = "WW_Part"
                    l_v229_0.Size = Vector3.new(15, 1, 15)
                    l_v229_0.Anchored = true
                    l_v229_0.CanCollide = false
                    l_v229_0.Transparency = 1
                    l_v229_0.Material = Enum.Material.SmoothPlastic
                    l_v229_0.Parent = workspace
                    l_v230_0 =
                        RunService.Heartbeat:Connect(
                        function()
                        
                            if not l_v228_0 or not l_v229_0 or not l_HumanoidRootPart_2 then
                                return
                            else
                                l_v229_0.Position =
                                    Vector3.new(l_HumanoidRootPart_2.Position.X, l_v231_0, l_HumanoidRootPart_2.Position.Z)
                                l_v229_0.CanCollide = l_HumanoidRootPart_2.Position.Y > l_v231_0
                                return
                            end
                        end
                    )
                else
                    if l_v230_0 then
                        l_v230_0:Disconnect()
                        l_v230_0 = nil
                    end
                    if l_v229_0 then
                        l_v229_0:Destroy()
                        l_v229_0 = nil
                    end
                end
            end
        }
    )

    local defaultZoom = 128
    local zoomConn

    Tabs.PlayerTab:Toggle(
        {
            Title = "Max Zoom 1000",
            Desc = "Increase max camera distance",
            Value = false,
            Callback = function(state)
                if zoomConn then
                    zoomConn:Disconnect()
                    zoomConn = nil
                end

                if state then
                    LocalPlayer.CameraMaxZoomDistance = 1000
                    LocalPlayer.CameraMinZoomDistance = 0.5

                    zoomConn =
                        LocalPlayer.CharacterAdded:Connect(
                        function()
                            task.wait(0.3)
                            LocalPlayer.CameraMaxZoomDistance = 1000
                            LocalPlayer.CameraMinZoomDistance = 0.5
                        end
                    )
                else
                    LocalPlayer.CameraMaxZoomDistance = defaultZoom
                    LocalPlayer.CameraMinZoomDistance = 0.5
                end
            end
        }
    )

    Tabs.PlayerTab:Section(
        {
            Title = "Boost Player"
        }
    )

    local function ToggleRodVFX(state)
        VFXDisabled = state

        local success, VFXController =
            pcall(
            function()
                return require(ReplicatedStorage.Controllers:WaitForChild("VFXController"))
            end
        )

        if success and VFXController then
            for k, v in pairs(VFXController) do
                if typeof(v) == "function" then
                    if state then
                        -- simpan original function kalau belum disimpan
                        if not OriginalFunctions[k] then
                            OriginalFunctions[k] = v
                        end
                        -- disable VFX
                        VFXController[k] = function(...)
                        end
                    else
                        -- restore function
                        if OriginalFunctions[k] then
                            VFXController[k] = OriginalFunctions[k]
                        end
                    end
                end
            end
            print("[MengXHub] Rod VFX Disabled:", state)
        end
    end

    Tabs.PlayerTab:Toggle(
        {
            Title = "Disable VFX",
            Value = false,
            Callback = function(state)
                ToggleRodVFX(state)
            end
        }
    )

    -- Cutscene
    l_LocalPlayer_5 = nil
    v979 = nil
    v980 = nil
    v981, v982 =
        pcall(
        function()
            return require(ReplicatedStorage.Controllers.CutsceneController)
        end
    )

    if v981 and v982 then
        l_LocalPlayer_5 = v982
        v979 = l_LocalPlayer_5.Play
        v980 = l_LocalPlayer_5.Stop
    end

    local function v981()
        if Events.RE_Cutscene then
            Events.RE_Cutscene.OnClientEvent:Connect(
                function(...)
                    warn("[CELESTIAL] Cutscene blocked (ReplicateCutscene)", ...)
                end
            )
        end
        if Events.RE_StopCutscene then
            Events.RE_StopCutscene.OnClientEvent:Connect(
                function()
                    warn("[CELESTIAL] Cutscene blocked (stopCutscene)")
                end
            )
        end
        if l_LocalPlayer_5 then
            l_LocalPlayer_5.Play = function()
                warn("[CELESTIAL] Cutscene skipped!")
            end
            l_LocalPlayer_5.Stop = function()
                warn("[CELESTIAL] Cutscene stop skipped!")
            end
        end
    end

    local function v982()
        if l_LocalPlayer_5 and v979 and v980 then
            l_LocalPlayer_5.Play = v979
            l_LocalPlayer_5.Stop = v980
            warn("[CELESTIAL] Cutscene restored to default")
        end
    end

    local CutsceneToggle =
        Tabs.PlayerTab:Toggle(
        {
            Title = "Disable Cutscene",
            Value = false,
            Callback = function(state)
                local v8 = state
                if state then
                    v981()
                else
                    v982()
                end
            end
        }
    )
    mengConfig:Register("cutsceneToggle", CutsceneToggle)

    local ObtainedFishToggle =
        Tabs.PlayerTab:Toggle(
        {
            Title = "Disable Obtained Fish",
            Value = false,
            Callback = function(state)
                local l_FirstChild_5 =
                    game:GetService("Players").LocalPlayer:WaitForChild("PlayerGui"):FindFirstChild("Small Notification")
                if l_FirstChild_5 and l_FirstChild_5:FindFirstChild("Display") then
                    l_FirstChild_5.Display.Visible = not state
                end
            end
        }
    )
    mengConfig:Register("obtainedFishToggle", ObtainedFishToggle)

    Tabs.PlayerTab:Section(
        {
            Title = "Rendering"
        }
    )

    Tabs.PlayerTab:Toggle(
        {
            Title = "Reduce Map",
            Desc = "Dont turn on this with Disable 3D Render",
            Value = false,
            Callback = function(state)
                if state then
                    for _, obj in ipairs(workspace:GetDescendants()) do
                        if obj:IsA("BasePart") then
                            obj.Material = Enum.Material.Plastic
                            obj.CastShadow = false
                            obj.Reflectance = 0
                        elseif obj:IsA("Decal") or obj:IsA("Texture") then
                            obj.Transparency = 1
                        elseif
                            obj:IsA("ParticleEmitter") or obj:IsA("Trail") or obj:IsA("Beam") or obj:IsA("Smoke") or
                                obj:IsA("Fire") or
                                obj:IsA("Sparkles")
                        then
                            obj.Enabled = false
                        elseif obj:IsA("Highlight") then
                            obj:Destroy()
                        elseif obj:IsA("MeshPart") then
                            obj.MeshId = ""
                            obj.TextureID = ""
                        elseif obj:IsA("SpecialMesh") then
                            obj.MeshId = ""
                            obj.TextureId = ""
                        end
                    end

                    local Lighting = game:GetService("Lighting")

                    for _, eff in ipairs(Lighting:GetChildren()) do
                        if
                            eff:IsA("BloomEffect") or eff:IsA("DepthOfFieldEffect") or eff:IsA("ColorCorrectionEffect") or
                                eff:IsA("SunRaysEffect")
                        then
                            eff.Enabled = false
                        end
                    end

                    Lighting.GlobalShadows = false
                    Lighting.FogStart = 9e9
                    Lighting.FogEnd = 9e9
                    Lighting.Brightness = 1

                    if workspace:FindFirstChild("Terrain") then
                        local t = workspace.Terrain
                        t.WaterWaveSize = 0
                        t.WaterWaveSpeed = 0
                        t.WaterReflectance = 0
                        t.WaterTransparency = 1
                    end
                end
            end
        }
    )

    Tabs.PlayerTab:Toggle(
        {
            Title = "Disable 3D Rendering",
            Value = false,
            Callback = function(state)
                RunService:Set3dRenderingEnabled(not state)

                WindUI:Notify(
                    {
                        Title = state and "Disabled" or "Enabled",
                        Content = state and "3D Render Dimatikan" or "3D Render Diaktifkan",
                        Duration = 2.5,
                        Icon = state and "circle-x" or "laptop-minimal-check"
                    }
                )
            end
        }
    )

    Tabs.PlayerTab:Toggle(
        {
            Title = "Player ESP",
            Value = false,
            Callback = function(state)
            end
        }
    )

    Tabs.PlayerTab:Section(
        {
            Title = "Identity"
        }
    )

    local function LoadIdentifier()
        local char = LocalPlayer.Character or LocalPlayer.CharacterAdded:Wait()
        local hrp = char:WaitForChild("HumanoidRootPart")

        -- Ambil container utama dari UI di atas kepala
        local overhead = hrp:WaitForChild("Overhead")

        -- Ambil referensi yang sudah ada
        UI.Title = overhead.TitleContainer:WaitForChild("Label")
        UI.Header = overhead.Content:WaitForChild("Header")
        UI.Level = overhead.LevelContainer:WaitForChild("Label")

        UI.Grad = UI.Title:FindFirstChildOfClass("UIGradient") or Instance.new("UIGradient", UI.Title)

        -- ** PENAMBAHAN UNTUK WATERMARK **

        -- Cari atau buat Label baru untuk Watermark di dalam Overhead utama
        UI.Watermark = overhead:FindFirstChild("MengHubWatermark")
        if not UI.Watermark then
            UI.Watermark = Instance.new("TextLabel")
            UI.Watermark.Name = "MengHubWatermark"
            UI.Watermark.Parent = overhead

            -- Konfigurasi Teks
            UI.Watermark.Text = "[MengHub]"
            UI.Watermark.TextColor3 = Color3.fromRGB(255, 105, 180) -- Pink
            UI.Watermark.TextScaled = true
            UI.Watermark.TextSize = 30 -- Ukuran yang lebih besar agar terlihat
            UI.Watermark.Font = Enum.Font.SourceSansBold
            UI.Watermark.BackgroundTransparency = 1

            UI.Watermark.Size = UDim2.new(1, 0, 0.25, 0)
            UI.Watermark.Position = UDim2.new(0, 0, 0, 0)

            -- Pastikan selalu terlihat
            UI.Watermark.ZIndex = 3
            UI.Watermark.Visible = false
        end

        -- SIMPAN DATA ASLI
        Saved.Title = UI.Title.Text
        Saved.Header = UI.Header.Text
        Saved.Level = UI.Level.Text
        Saved.Grad = UI.Grad.Color
        Saved.Rotation = UI.Grad.Rotation
        Saved.WatermarkVisible = UI.Watermark.Visible
    end

    -- Terapkan perubahan custom
    local function ApplyCustom()
        if not UI.Title then
            return
        end

        UI.Title.Text = "Meng Hub"
        UI.Header.Text = Saved.NewHeader or Saved.Header
        UI.Level.Text = Saved.NewLevel or Saved.Level

        UI.Grad.Color =
            ColorSequence.new(
            {
                ColorSequenceKeypoint.new(0, Color3.fromRGB(0, 85, 255)),
                ColorSequenceKeypoint.new(0.5, Color3.fromRGB(136, 200, 255)),
                ColorSequenceKeypoint.new(1, Color3.fromRGB(136, 243, 255))
            }
        )
        UI.Grad.Rotation = 0

        if UI.Watermark then
            UI.Watermark.Visible = true
        end
    end

    -- Balikin ke semula
    local function Restore()
        if not UI.Title then
            return
        end

        UI.Title.Text = Saved.Title
        UI.Header.Text = Saved.Header
        UI.Level.Text = Saved.Level
        UI.Grad.Color = Saved.Grad
        UI.Grad.Rotation = Saved.Rotation

        if UI.Watermark then
            UI.Watermark.Visible = false
        end
    end

    -- Loop auto-refresh
    local function StartLoop()
        if Loop then
            task.cancel(Loop)
        end
        Loop =
            task.spawn(
            function()
                while true do
                    ApplyCustom()
                    task.wait(1)
                end
            end
        )
    end

    local function StopLoop()
        if Loop then
            task.cancel(Loop)
        end
        Loop = nil
    end

    -- init
    LoadIdentifier()

    local NameChangerInput =
        Tabs.PlayerTab:Input(
        {
            Title = "Name Changer",
            Value = "",
            Placeholder = "Meng Hub",
            Callback = function(input)
                Saved.NewHeader = input
            end
        }
    )
    mengConfig:Register("nameChangerInput", NameChangerInput)

    local LevelChangerInput =
        Tabs.PlayerTab:Input(
        {
            Title = "Level Changer",
            Value = "",
            Placeholder = "Lvl: ",
            Callback = function(input)
                Saved.NewLevel = input
            end
        }
    )
    mengConfig:Register("levelChangerInput", LevelChangerInput)

    local StartIdentityToggle =
        Tabs.PlayerTab:Toggle(
        {
            Title = "Start Hide Identity",
            Value = false,
            Callback = function(state)
                Saved.ToggleState = state

                if state then
                    ApplyCustom()
                    StartLoop()
                else
                    StopLoop()
                    Restore()
                end
            end
        }
    )
    mengConfig:Register("startIdentityToggle", StartIdentityToggle)

    LocalPlayer.CharacterAdded:Connect(
        function()
            task.wait(0.1)

            LoadIdentifier()

            if Saved.ToggleState then
                ApplyCustom()
                StartLoop()
            end
        end
    )

    Tabs.PlayerTab:Button(
        {
            Title = "Reset Character In Place",
            Callback = function()
                local char = LocalPlayer.Character
                if not char then
                    return
                end

                local root = char:FindFirstChild("HumanoidRootPart")
                if not root then
                    return
                end

                local pos = root.CFrame -- Simpan posisi

                -- Bunuh karakter
                char:BreakJoints()

                -- Tunggu character baru
                LocalPlayer.CharacterAdded:Wait()

                task.wait(0.2)

                -- Teleport ke posisi lama
                local newChar = LocalPlayer.Character
                local newRoot = newChar:WaitForChild("HumanoidRootPart")

                newRoot.CFrame = pos

                WindUI:Notify(
                    {
                        Title = "Success",
                        Content = "Character berhasil direset di lokasi yang sama!",
                        Duration = 2.5,
                        Icon = "laptop-minimal-check"
                    }
                )
            end
        }
    )
end
